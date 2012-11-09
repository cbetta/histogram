require 'sinatra'

set :env,  :production
disable :run

require './histogram.rb'

run Sinatra::Application