package com.trustledger.paymentcore.api.dto;

public record PostLedgerResponseDto(
    boolean ok,
    boolean duplicate
) {}