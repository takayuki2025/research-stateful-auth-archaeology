package com.trustledger.paymentcore.api.dto;

import jakarta.validation.constraints.NotBlank;
import jakarta.validation.constraints.NotNull;
import jakarta.validation.constraints.Positive;

import java.util.Map;

public record PostLedgerCommandDto(
    @NotBlank String source_provider,
    @NotBlank String source_event_id,

    @NotNull Integer shop_id,
    Integer order_id,
    Integer payment_id,

    @NotBlank String posting_type,

    @NotNull @Positive Integer amount,
    @NotBlank String currency,

    // "YYYY-MM-DD HH:mm:ss" 想定（Laravelがそう送っている）
    @NotBlank String occurred_at,

    Map<String, Object> meta,
    @NotNull Boolean replay
) {}