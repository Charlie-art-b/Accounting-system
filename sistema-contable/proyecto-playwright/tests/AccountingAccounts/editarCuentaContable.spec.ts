import { test, expect } from '@playwright/test';

test('Editar cuenta contable', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin/login');
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('ad');
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).click();
  await page.getByRole('textbox', { name: 'Correo electrónico*' }).fill('admin@sistema.com');
  await page.getByRole('textbox', { name: 'Contraseña*' }).click();
  await page.getByRole('textbox', { name: 'Contraseña*' }).fill('1234');
  await page.getByRole('button', { name: 'Entrar' }).click();
  await page.getByRole('link', { name: 'Cuentas Contables' }).click();
  await page.goto('http://127.0.0.1:8000/admin/accounting-accounts');
  await page.getByRole('link', { name: 'Editar' }).nth(1).click();
  await page.getByLabel('Tipo*').selectOption('Ingreso');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await page.getByRole('button', { name: 'Sí, guardar' }).click();
  await page.locator('.fi-icon-btn.fi-no-notification-close-btn').click();
});