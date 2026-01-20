require "date"

module Admin
  module Trustledger
    class KpisController < ApplicationController
      def global
        client = ::TrustLedger::AdminApi::Client.new

        from, to = normalize_range(params)
        @data = client.get_global_kpis(from: from, to: to)
      rescue ::TrustLedger::AdminApi::Error => e
        @error = { message: e.message, status: e.status, body: e.body }
      end

      def shops
        client = ::TrustLedger::AdminApi::Client.new

        from, to = normalize_range(params)
        @data = client.get_shop_kpis(from: from, to: to, limit: (params[:limit].presence || 20))
      rescue ::TrustLedger::AdminApi::Error => e
        @error = { message: e.message, status: e.status, body: e.body }
      end

      private

      def normalize_range(p)
        default_from = (Date.today - 7).strftime("%Y-%m-%d")
        default_to   = Date.today.strftime("%Y-%m-%d")
        [p[:from].presence || default_from, p[:to].presence || default_to]
      end
    end
  end
end