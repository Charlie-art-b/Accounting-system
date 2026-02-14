import { test, expect } from '@playwright/test';

test('Deshacer pago', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Gestión De Cobros' }).click();
  await page.getByRole('button', { name: 'Deshacer pago' }).click();
  await page.getByRole('button', { name: 'Seleccione una opción' }).click();
  await page.getByText('📅 2026-02-13 - ₡9,000.00 -').click();
  await page.getByRole('button', { name: 'Confirmar' }).click();
});