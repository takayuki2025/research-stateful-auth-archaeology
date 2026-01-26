module Admin
  module Trustledger
    class ReviewQueueController < ApplicationController
      def index
        client = TrustLedger::AdminApi::Client.new
        @params = {
          queue_type: params[:queue_type].presence,
          status: params[:status].presence,
          limit: (params[:limit].presence || "50"),
          offset: (params[:offset].presence || "0"),
        }

        res = client.list_review_queue(@params)
        @items = res["items"] || []
      rescue TrustLedger::AdminApi::Error => e
        @error = e
        @items = []
      end

      def show
        client = TrustLedger::AdminApi::Client.new
        @item = client.get_review_queue_item(params.require(:id))
      rescue TrustLedger::AdminApi::Error => e
        @error = e
        @item = nil
      end

      def decide
  client = TrustLedger::AdminApi::Client.new
  id = params.require(:id)

  payload = {
    action: params.require(:action),
    note: params[:note].presence,
  }.compact

  # optional: checklist json
  if params[:extra_checklist_json].present?
    begin
      checklist = JSON.parse(params[:extra_checklist_json])
      payload[:extra] = { checklist: checklist }
    rescue JSON::ParserError
      return redirect_to(
        "/admin/dashboard/trustledger/review-queue/#{id}",
        alert: "Invalid JSON in extra.checklist"
      )
    end
  end

  client.decide_review_queue_item(id, payload)
  redirect_to "/admin/dashboard/trustledger/review-queue/#{id}", notice: "Decided."
rescue ActionController::ParameterMissing => e
  redirect_to "/admin/dashboard/trustledger/review-queue/#{params[:id]}", alert: "Missing: #{e.param}"
rescue TrustLedger::AdminApi::Error => e
  redirect_to "/admin/dashboard/trustledger/review-queue/#{params[:id]}", alert: "API error: #{e.status}"
end
    end
  end
end