module Admin
  module Trustledger
    class HealthController < ApplicationController
      def show
        client = ::TrustLedger::AdminApi::Client.new
        @data = client.get_health
      rescue ::TrustLedger::AdminApi::Error => e
        @error = { message: e.message, status: e.status, body: e.body }
      end
    end
  end
end