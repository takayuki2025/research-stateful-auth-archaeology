Rails.application.routes.draw do
  get "up" => "rails/health#show", as: :rails_health_check

  namespace :admin do
    namespace :trustledger do
      get "/", to: "dashboard#show"
      get "/health", to: "health#show"

      # ✅ 追加：TL-30 Webhook Events list
      get "/webhook-events", to: "webhook_events#index"
      get "/webhook-events/:event_id", to: "webhook_events#show"
      post "/webhook-events/:event_id/replay", to: "webhook_events#replay"

      # 5.1 Global KPI
      get  "/kpis/global", to: "kpis#global"
      get  "/kpis/shops",  to: "kpis#shops"

# 5.2 Postings
      get  "/postings",              to: "postings#index"
      get  "/postings/:posting_id",  to: "postings#show"

# 5.3 Reconciliation
      get  "/reconciliation/missing-sales", to: "reconciliation#missing_sales"
      post "/reconciliation/replay/sale",   to: "reconciliation#replay_sale"
    end
  end
end