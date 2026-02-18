import { test, expect } from '@playwright/test';

test('Crear Cuenta por Pagar', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Cuentas Por Pagar' }).click();
  await page.getByRole('link', { name: 'Crear Cuenta por pagar' }).click();
  await page.getByRole('button', { name: 'Seleccione una opción' }).click();
  await page.getByText('Juan Carlos Ramírez López').click();
  await page.getByText('Juan Carlos Ramírez López').click();
  await page.getByText('Juan Carlos Ramírez López').click();
  await page.getByRole('textbox', { name: 'Número de Documento*' }).click();
  await page.getByRole('textbox', { name: 'Número de Documento*' }).fill('505050');
  await page.getByRole('spinbutton', { name: 'Período de Pago (días)' }).click();
  await page.getByRole('spinbutton', { name: 'Período de Pago (días)' }).fill('12');
  await page.getByRole('textbox', { name: 'Fecha de Vencimiento*' }).click();
  await page.locator('.fi-sc.fi-sc-has-gap.fi-grid.lg\\:fi-grid-cols').first().click();
  await page.getByRole('spinbutton', { name: 'Monto Total*' }).click();
  await page.getByRole('spinbutton', { name: 'Monto Total*' }).fill('18000');
  await page.getByRole('spinbutton', { name: 'Monto Pagado*' }).click();
  await page.getByRole('spinbutton', { name: 'Monto Pagado*' }).fill('1200');
  await page.getByRole('textbox', { name: 'Fecha de Pago' }).click();
  await page.getByRole('option', { name: '16' }).click();
  await page.getByRole('button', { name: 'Crear', exact: true }).click();
});