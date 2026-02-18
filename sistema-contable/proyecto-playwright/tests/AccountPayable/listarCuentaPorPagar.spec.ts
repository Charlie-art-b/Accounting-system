import { test, expect } from '@playwright/test';

test('Listar Cuentas por Pagar', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Cuentas Por Pagar' }).click();
  await page.getByRole('button', { name: 'Filtrar' }).click();
  await page.getByRole('checkbox', { name: 'Con saldo pendiente' }).check();
  await page.getByRole('button', { name: 'Aplicar filtros' }).click();
  await page.getByRole('main').click();
  await page.locator('a:nth-child(2)').click();
});