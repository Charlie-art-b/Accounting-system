import { test, expect } from '@playwright/test';

test('Eliminar cuenta contable', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin/login');
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).click();
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('admin@sistema.com');
  await page.getByRole('textbox', { name: 'Contraseña*' }).click();
  await page.getByRole('textbox', { name: 'Contraseña*' }).fill('1234');
  await page.getByRole('button', { name: 'Entrar' }).click();
  await page.getByRole('link', { name: 'Cuentas Contables' }).click();
  await page.goto('http://127.0.0.1:8000/admin/accounting-accounts');
  await page.getByRole('checkbox', { name: 'Seleccionar/deseleccionar el elemento 7 para las acciones masivas.' }).check();
  await page.locator('#key-bindings-8').click();
  await page.locator('.fi-color.fi-color-danger.fi-bg-color-400').click();
  await page.locator('.fi-icon-btn.fi-no-notification-close-btn').click();
});