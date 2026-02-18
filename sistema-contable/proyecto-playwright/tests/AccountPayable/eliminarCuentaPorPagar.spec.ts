import { test, expect } from '@playwright/test';

test('Eliminar Cuenta por Pagar', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Cuentas Por Pagar' }).click();
  await page.getByRole('checkbox', { name: 'Seleccionar/deseleccionar el' }).check();
  await page.getByRole('button', { name: 'Abrir acciones' }).click();
  await page.getByRole('button', { name: 'Borrar seleccionados' }).click();
  await page.getByRole('button', { name: 'Borrar', exact: true }).click();
  await page.locator('.fi-icon-btn.fi-no-notification-close-btn').click();
});