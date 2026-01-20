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
    end
  end
end