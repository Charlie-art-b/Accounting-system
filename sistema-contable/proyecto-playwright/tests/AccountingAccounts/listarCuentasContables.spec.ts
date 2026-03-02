import { test, expect } from '@playwright/test';

test('Listar cuentas contables', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin/login');
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).click();
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('admin@sistema.com');
  await page.getByRole('textbox', { name: 'Contraseña*' }).click();
  await page.getByRole('textbox', { name: 'Contraseña*' }).fill('1234');
  await page.getByRole('button', { name: 'Entrar' }).click();
  await page.getByRole('link', { name: 'Cuentas Contables' }).click();
  await page.goto('http://127.0.0.1:8000/admin/accounting-accounts');
  await page.getByRole('button', { name: 'Filtrar' }).click();
  await page.locator('[id="tableFiltersForm.type.value"]').selectOption('Activo');
  await page.getByRole('button', { name: 'Aplicar filtros' }).click();
  await page.getByRole('main').click();
});