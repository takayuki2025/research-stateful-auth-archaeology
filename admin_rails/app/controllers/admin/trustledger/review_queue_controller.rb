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

        # 1) ReviewQueue item
        @item = client.get_review_queue_item(params.require(:id))

        summary = @item.is_a?(Hash) ? (@item["summary"] || {}) : {}
        after_document_id = summary["after_document_id"]
        diff_id = summary["diff_id"]

        # 2) Extracted document
        @doc = nil
        if after_document_id
          begin
            @doc = client.get_providerintel_document(after_document_id)
          rescue TrustLedger::AdminApi::Error
            @doc = nil
          end
        end

        # 3) Diff summary
        @diff = nil
        if diff_id
          begin
            @diff = client.get_providerintel_diff(diff_id)
          rescue TrustLedger::AdminApi::Error
            @diff = nil
          end
        end

        # 4) requests_for_info: open の最新 1件だけ
        @latest_open_rfi = nil
        rfis = @item["requests_for_info"]
        if rfis.is_a?(Array)
          open = rfis.select { |r| r.is_a?(Hash) && r["status"] == "open" }
          # requested_at / created_at が文字列なので max_by で十分
          @latest_open_rfi = open.max_by do |r|
            (r["requested_at"] || r["created_at"] || "") + (r["id"] || 0).to_s
          end
        end

      rescue TrustLedger::AdminApi::Error => e
        @error = e
        @item = nil
        @doc = nil
        @diff = nil
        @latest_open_rfi = nil
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