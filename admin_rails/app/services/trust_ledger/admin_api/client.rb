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

      def get_health
        get_json("/api/admin/trustledger/health")
      end

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

    # --- KPI ---
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

# --- Postings ---
def search_postings(params = {})
  query = URI.encode_www_form(params.compact)
  path = "/api/admin/trustledger/postings"
  path += "?#{query}" unless query.empty?
  get_json(path)
end

def get_posting_detail(posting_id)
  get_json("/api/admin/trustledger/postings/#{posting_id}")
end

# --- Reconciliation ---
def list_missing_sales(params = {})
  query = URI.encode_www_form(params.compact)
  path = "/api/admin/trustledger/reconciliation/missing-sales"
  path += "?#{query}" unless query.empty?
  get_json(path)
end

def replay_sale(params = {})
  post_json("/api/admin/trustledger/replay/sale", params)
end


def post_json(path, payload)
  uri = URI.parse(@base_url + path)
  http = Net::HTTP.new(uri.host, uri.port)
  http.read_timeout = 10

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
  # ✅ ここが重要：自前Errorは包まず、そのまま投げる（status/bodyを保持）
  raise e

rescue JSON::ParserError
  raise Error.new("Invalid JSON response", status: res&.code&.to_i, body: body)

rescue => e
  raise Error.new("Request failed: #{e.class}: #{e.message}")
end
    end
  end
end