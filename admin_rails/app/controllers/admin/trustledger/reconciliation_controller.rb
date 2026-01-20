require "date"

module Admin
  module Trustledger
    class ReconciliationController < ApplicationController
      def missing_sales
        client = ::TrustLedger::AdminApi::Client.new

        default_from = (Date.today - 30).strftime("%Y-%m-%d")
        default_to   = Date.today.strftime("%Y-%m-%d")

        api_params = {
          from: params[:from].presence || default_from,
          to: params[:to].presence || default_to,
          shop_id: params[:shop_id].presence,
          limit: params[:limit].presence || 50,
        }.compact

        @data = client.list_missing_sales(api_params)
        @items = @data["items"] || @data["missing_sales"] || []
      rescue ::TrustLedger::AdminApi::Error => e
        @error = { message: e.message, status: e.status, body: e.body }
      end

      def replay_sale
        client = ::TrustLedger::AdminApi::Client.new

        # 最小入力：payment_id / order_id / provider_payment_id のどれかを backend 仕様に合わせて
        payload = {
          shop_id: params[:shop_id].presence,
          payment_id: params[:payment_id].presence,
          order_id: params[:order_id].presence,
          provider_payment_id: params[:provider_payment_id].presence,
        }.compact

        result = client.replay_sale(payload)
        flash[:notice] = "Replay sale OK: #{result.inspect}"
      rescue ::TrustLedger::AdminApi::Error => e
        flash[:alert] = "Replay sale FAILED: #{e.status || "?"} #{e.body.presence || e.message}"
      ensure
        redirect_to action: :missing_sales, from: params[:from], to: params[:to]
      end
    end
  end
end