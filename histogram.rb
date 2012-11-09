require 'sinatra'
require 'mini_magick'
require 'chunky_png'
require 'redis'
require 'yaml'

GOOGLE_CHARTS_URL = "http://chart.apis.google.com/chart?cht=ls&chd=s:%s&chco=%s&chls=%s&chs=%sx%s".freeze
DATA_ENCODING = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789".freeze

get '/' do
  return redirect "http://blog.cristianobetta.com/2008/08/31/photo-histograms-everywhere/" unless params[:image]

  params[:height] ||= 175;
  params[:height] = params[:height].to_i
  params[:height] = 385 if params[:height] > 385
  params[:height] = 10 if params[:height] < 10
  params[:width] = (2.02*params[:height]).floor

  image_url = cached_image_url || read_image_and_build_url

  if params[:type] == 'html'
    return "<img src='#{image_url}' />"
  elsif params[:type] == 'js'
    return "function getSrc(){return '#{image_url}'}"
  else
    redirect URI.encode(image_url)
  end
end

private

def redis
  @redis = Redis.new(YAML.load_file('config/redis.yml')[ENV['RACK_ENV']])
end

def cached_image_url
  redis.get(request.query_string)
end

def read_image_and_build_url
  data = count_pixels params[:image]
  image_url = build_url data
  redis.set(request.query_string, image_url)
  image_url
end

def count_pixels url
  image = MiniMagick::Image.open(url)
  image.format('png')

  png = ChunkyPNG::Image.from_io(StringIO.new(image.to_blob))

  data = {
    all: Hash.new(0),
    red: Hash.new(0),
    green: Hash.new(0),
    blue: Hash.new(0)
  }

  image_size = png.height * png.height

  png.height.times do |y|
    png.width.times do |x|
      colors = ChunkyPNG::Color.to_truecolor_bytes(png[x, y])
      red = colors[0].to_f
      green = colors[1].to_f
      blue = colors[2].to_f

      luminance = (0.3*red + 0.59*green + 0.11*blue).round.to_f

      luminance_index = (luminance - luminance%3)/3;
      red_index = (red - red%3)/3;
      green_index = (green - green%3)/3;
      blue_index = (blue - blue%3)/3;

      data[:all][luminance_index] += luminance/image_size
      data[:red][red_index] += red/image_size
      data[:green][green_index] += green/image_size
      data[:blue][blue_index] += blue/image_size
    end
  end

  data
end

def build_url data
  if params[:rgb] == "true"
    max = data.map{|key, value| value.values.max }.max
    red_data = google_encode data[:red], max
    green_data = google_encode data[:green], max
    blue_data = google_encode data[:blue], max
    encoded_data = [red_data, green_data, blue_data].join(",")
    color = "c21f1fAA,99c274AA,519bc2AA"
    format = "2,5,0|2,5,0|2,5,0"
  else
    encoded_data = google_encode data[:all], data[:all].values.max
    color = "AAAAAA"
    format = "2,5,0"
  end

  width = params[:width]
  height = params[:height]

  GOOGLE_CHARTS_URL % [encoded_data, color, format, width, height]
end

def google_encode data, max
  encoded_data = ""

  85.times do |i|
    entry = data[i.to_f]
    if entry.nil?
      encoded_data += "_"
    else
      index = 61*(entry.to_f/max)
      encoded_data += DATA_ENCODING[index, 1]
    end
  end
  encoded_data
end
