# Flujo principal: bot de Telegram

1. Usuario envía foto/PDF/CSV al bot de Telegram.
2. Webhook en Symfony recibe la actualización, identifica al usuario por `telegram_chat_id`.
3. Descarga el fichero vía API de Telegram, crea `Document` con `source=telegram`.
4. Despacha command asíncrono vía Messenger.
5. Worker: procesa documento (OCR vía Ollama para imágenes, parser para CSV/PDF).
6. Worker: aplica pipeline de clasificación (reglas → IA).
7. Worker: al terminar, envía resumen al chat de Telegram via `NotificationInterface`.

Formato del resumen para recibo individual:
```
📄 Recibo procesado
🏦 BBVA — Cuenta familiar
👤 María
📅 15/08/2026
💰 -45,30 €
🏷 Mercadona → Alimentación
✅ Clasificado automáticamente (confianza: 97%)
```

Formato para extractos con múltiples movimientos:
```
📄 Extracto procesado
🏦 BBVA — Cuenta familiar
📊 47 movimientos importados
✅ 38 clasificados automáticamente
⚠️ 7 requieren revisión
🔄 2 posibles duplicados
🔗 Ver detalle en la web: [enlace]
```

**Evolución futura:** botones inline de Telegram para correcciones (cambiar categoría, confirmar, descartar, marcar duplicado). Se implementará cuando el flujo básico esté consolidado.
