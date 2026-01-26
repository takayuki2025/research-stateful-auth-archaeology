require "net/http"
require "json"
require "uri"

module TrustLedger
  module AdminApi
    class Error < StandardError
      attr_reader :status, :body
      def initialize(message, status: nil, body: nil)
        super(message)
        @status = status
        @body = body
      end
    end

    class Client
      def initialize(
        base_url: ENV.fetch("TRUSTLEDGER_ADMIN_API_BASE_URL"),
        admin_key: ENV.fetch("TRUSTLEDGER_ADMIN_X_ADMIN_KEY")
      )
        @base_url = base_url.sub(%r{/\z}, "")
        @admin_key = admin_key
      end

      # =========================================================
      # TrustLedger: Health
      # =========================================================
      def get_health
        get_json("/api/admin/trustledger/health")
      end

      # =========================================================
      # TrustLedger: Webhook Events
      # =========================================================
      def list_webhook_events(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/trustledger/webhooks/events"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      def get_webhook_event(event_id)
        get_json("/api/admin/trustledger/webhooks/events/#{event_id}")
      end

      def replay_webhook_event(event_id)
        post_json("/api/admin/trustledger/webhooks/events/#{event_id}/replay", {})
      end

      # =========================================================
      # TrustLedger: KPI
      # =========================================================
      def get_global_kpis(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/trustledger/kpis/global"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      def get_shop_kpis(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/trustledger/kpis/shops"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      # =========================================================
      # TrustLedger: Postings
      # =========================================================
      def search_postings(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/trustledger/postings"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      def get_posting_detail(posting_id)
        get_json("/api/admin/trustledger/postings/#{posting_id}")
      end

      # =========================================================
      # TrustLedger: Reconciliation
      # =========================================================
      def list_missing_sales(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/trustledger/reconciliation/missing-sales"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      def replay_sale(params = {})
        post_json("/api/admin/trustledger/replay/sale", params)
      end

      # =========================================================
      # ✅ ProviderIntel v1: Catalog Sources (4 methods)
      # =========================================================

      # GET /api/admin/providerintel/sources?provider_id=&status=&limit=&offset=
      def list_catalog_sources(params = {})
        query = URI.encode_www_form(params.compact)
        path = "/api/admin/providerintel/sources"
        path += "?#{query}" unless query.empty?
        get_json(path)
      end

      # GET /api/admin/providerintel/sources/:id
      def get_catalog_source(source_id)
        get_json("/api/admin/providerintel/sources/#{source_id}")
      end

      # POST /api/admin/providerintel/sources
      # payload example:
      # {
      #   provider_id: 1,
      #   source_type: "pdf",
      #   source_url: "https://example.com/fees.pdf",
      #   update_frequency: "weekly",
      #   status: "active",
      #   notes: "Stripe JP fees"
      # }
      def upsert_catalog_source(payload)
        post_json("/api/admin/providerintel/sources", payload)
      end

      # POST /api/admin/providerintel/sources/:id/run
      def run_catalog_source(source_id)
        post_json("/api/admin/providerintel/sources/#{source_id}/run", {})
      end



      # =========================================================
# Review Queue (v3.3)
# =========================================================

# GET /api/admin/review-queue?queue_type=&status=&limit=&offset=
def list_review_queue(params = {})
  query = URI.encode_www_form(params.compact)
  path = "/api/admin/review-queue"
  path += "?#{query}" unless query.empty?
  get_json(path)
end

# GET /api/admin/review-queue/:id
def get_review_queue_item(id)
  get_json("/api/admin/review-queue/#{id}")
end

# POST /api/admin/review-queue/:id/decide
# payload: { action: "approve|reject|request_more_info", note: "...", extra: {...} }
def decide_review_queue_item(id, payload)
  post_json("/api/admin/review-queue/#{id}/decide", payload)
end


def get_providerintel_document(id)
  get_json("/api/admin/providerintel/documents/#{id}")
end


      # =========================================================
      # Internal HTTP helpers
      # =========================================================
      def post_json(path, payload)
        uri = URI.parse(@base_url + path)
        http = Net::HTTP.new(uri.host, uri.port)
        http.read_timeout = 10
        http.use_ssl = (uri.scheme == "https")

        req = Net::HTTP::Post.new(uri.request_uri)
        req["Accept"] = "application/json"
        req["Content-Type"] = "application/json"
        req["X-Admin-Key"] = @admin_key
        req.body = JSON.dump(payload)

        res = http.request(req)
        body = res.body.to_s

        if res.code.to_i >= 400
          raise Error.new("Admin API error", status: res.code.to_i, body: body)
        end

        JSON.parse(body)
      rescue Error => e
        raise e
      rescue JSON::ParserError
        raise Error.new("Invalid JSON response", status: res&.code&.to_i, body: body)
      rescue => e
        raise Error.new("Request failed: #{e.class}: #{e.message}")
      end

      private

      def get_json(path)
        uri = URI.parse(@base_url + path)
        http = Net::HTTP.new(uri.host, uri.port)
        http.read_timeout = 10
        http.use_ssl = (uri.scheme == "https")

        req = Net::HTTP::Get.new(uri.request_uri)
        req["Accept"] = "application/json"
        req["X-Admin-Key"] = @admin_key

        res = http.request(req)
        body = res.body.to_s

        if res.code.to_i >= 400
          raise Error.new("Admin API error", status: res.code.to_i, body: body)
        end

        JSON.parse(body)
      rescue Error => e
        raise e
      rescue JSON::ParserError
        raise Error.new("Invalid JSON response", status: res&.code&.to_i, body: body)
      rescue => e
        raise Error.new("Request failed: #{e.class}: #{e.message}")
      end
    end
  end
end