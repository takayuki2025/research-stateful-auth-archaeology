module Admin
  module Trustledger
    class ProviderintelSourcesController < ApplicationController
      def index
        client = TrustLedger::AdminApi::Client.new
        @params = {
          provider_id: params[:provider_id].presence,
          status: params[:status].presence,
          limit: (params[:limit].presence || "50"),
          offset: (params[:offset].presence || "0"),
        }

        res = client.list_catalog_sources(@params)
        @items = res["items"] || []
      rescue TrustLedger::AdminApi::Error => e
        @error = e
        @items = []
      end

      def new
        # form
      end

      def create
        client = TrustLedger::AdminApi::Client.new

        payload = {
          id: params[:id].presence,
          project_id: params[:project_id].presence,
          provider_id: params.require(:provider_id),
          source_type: params.require(:source_type),
          source_url: params[:source_url].presence,
          update_frequency: params.require(:update_frequency),
          status: params.require(:status),
          notes: params[:notes].presence,
        }.compact

        client.upsert_catalog_source(payload)
        redirect_to "/admin/dashboard/trustledger/providerintel/sources", notice: "Saved."
      rescue ActionController::ParameterMissing => e
        redirect_to "/admin/dashboard/trustledger/providerintel/sources/new", alert: "Missing: #{e.param}"
      rescue TrustLedger::AdminApi::Error => e
        @error = e
        render :new, status: 422
      end

      def run
        client = TrustLedger::AdminApi::Client.new
        source_id = params.require(:source_id)

        client.run_catalog_source(source_id)
        redirect_to "/admin/dashboard/trustledger/providerintel/sources", notice: "Run triggered."
      rescue TrustLedger::AdminApi::Error => e
        redirect_to "/admin/dashboard/trustledger/providerintel/sources", alert: "Run failed: #{e.status}"
      end

      def show
  client = TrustLedger::AdminApi::Client.new
  @item = client.get_catalog_source(params.require(:source_id))
rescue TrustLedger::AdminApi::Error => e
  @error = e
  @item = nil
end

def edit
  client = TrustLedger::AdminApi::Client.new
  @item = client.get_catalog_source(params.require(:source_id))
rescue TrustLedger::AdminApi::Error => e
  @error = e
  @item = nil
  render :show, status: 404
end

def update
  client = TrustLedger::AdminApi::Client.new
  source_id = params.require(:source_id)

  payload = {
    id: source_id,
    project_id: params[:project_id].presence,
    provider_id: params.require(:provider_id),
    source_type: params.require(:source_type),
    source_url: params[:source_url].presence,
    update_frequency: params.require(:update_frequency),
    status: params.require(:status),
    notes: params[:notes].presence,
  }.compact

  client.upsert_catalog_source(payload)
  redirect_to "/admin/dashboard/trustledger/providerintel/sources/#{source_id}", notice: "Updated."
rescue ActionController::ParameterMissing => e
  redirect_to "/admin/dashboard/trustledger/providerintel/sources/#{params[:source_id]}/edit", alert: "Missing: #{e.param}"
rescue TrustLedger::AdminApi::Error => e
  @error = e
  @item = payload
  render :edit, status: 422
end
    end
  end
end