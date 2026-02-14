import { test, expect } from '@playwright/test';

test('Registrar pago', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Gestión De Cobros' }).click();
  await page.getByRole('button', { name: 'Registrar pago' }).click();
  await page.getByRole('spinbutton', { name: 'Monto a pagar*' }).click();
  await page.getByRole('spinbutton', { name: 'Monto a pagar*' }).fill('9000');
  await page.getByRole('textbox', { name: 'Nota (opcional)' }).click();
  await page.getByRole('textbox', { name: 'Nota (opcional)' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nota (opcional)' }).fill('A');
  await page.getByRole('textbox', { name: 'Nota (opcional)' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nota (opcional)' }).fill('Abono de pago de bici');
  await page.getByRole('button', { name: 'Confirmar' }).click();
  await page.locator('.fi-icon-btn.fi-no-notification-close-btn').click();
});