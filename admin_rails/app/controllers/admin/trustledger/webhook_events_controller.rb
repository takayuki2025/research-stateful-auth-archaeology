require "date"

module Admin
  module Trustledger
    class WebhookEventsController < ApplicationController
        SESSION_STACK_KEY   = "tl_webhook_cursor_stack"
        SESSION_FILTER_KEY  = "tl_webhook_filters"
        SESSION_CURSOR_KEY  = "tl_webhook_current_cursor"

      def index
        client = ::TrustLedger::AdminApi::Client.new

        default_from = (Date.today - 7).strftime("%Y-%m-%d")
        default_to   = Date.today.strftime("%Y-%m-%d")

        from     = params[:from].presence || default_from
        to       = params[:to].presence   || default_to
        per_page = (params[:per_page].presence || "50").to_s

        # ✅ session と比較できるように「文字列キー」で統一
        filters = {
          "from"       => from,
          "to"         => to,
          "event_type" => params[:event_type].presence,
          "status"     => params[:status].presence,
          "per_page"   => per_page,
        }

        # フィルタが変わったらスタックをリセット
        if session[SESSION_FILTER_KEY] != filters
          session[SESSION_FILTER_KEY] = filters
          session[SESSION_STACK_KEY] = []
        end

        def show
  client = ::TrustLedger::AdminApi::Client.new
  @data = client.get_webhook_event(params[:event_id])
rescue ::TrustLedger::AdminApi::Error => e
  @error = { message: e.message, status: e.status, body: e.body }
end

        stack = (session[SESSION_STACK_KEY] ||= [])

nav = params[:nav].to_s # "older" / "newer" / ""
current_cursor = session[SESSION_CURSOR_KEY] # ✅ 現在カーソルは session 主語

if nav == "older"
  stack << current_cursor
  current_cursor = params[:next_cursor].presence
elsif nav == "newer"
  current_cursor = stack.pop
end

        def replay
  client = ::TrustLedger::AdminApi::Client.new
  @data = client.replay_webhook_event(params[:event_id])
rescue ::TrustLedger::AdminApi::Error => e
  @error = { message: e.message, status: e.status, body: e.body }
ensure
  redirect_to action: :show, event_id: params[:event_id]
end

session[SESSION_STACK_KEY] = stack
session[SESSION_CURSOR_KEY] = current_cursor

        api_params = {
          from: filters["from"],
          to: filters["to"],
          event_type: filters["event_type"],
          status: filters["status"],
          per_page: filters["per_page"],
          cursor: current_cursor,
        }.compact

        @data = client.list_webhook_events(api_params)
        @items = @data["items"] || []
        @next_cursor = @data["next_cursor"]

        @cursor = current_cursor
        @stack_size = stack.size
        @nav = nav
      rescue ::TrustLedger::AdminApi::Error => e
        @error = { message: e.message, status: e.status, body: e.body }
      end
    end
  end
end