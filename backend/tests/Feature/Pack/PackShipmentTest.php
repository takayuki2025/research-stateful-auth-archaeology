public function test_pack_shipment(): void
{
    $shipment = Shipment::factory()->create([
        'status' => 'created',
    ]);

    app(PackShipmentUseCase::class)->handle($shipment->id);

    $this->assertDatabaseHas('shipments', [
        'id' => $shipment->id,
        'status' => 'packed',
    ]);

    $this->assertDatabaseHas('shipment_events', [
        'shipment_id' => $shipment->id,
        'type' => 'packed',
    ]);
}