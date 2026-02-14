import { test, expect } from '@playwright/test';

test('Validacion espacios en blanco', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Clientes' }).click();
  await page.getByRole('link', { name: 'Crear Cliente' }).click();
  await page.getByRole('button', { name: 'Crear' }).click();
  await page.getByRole('button', { name: 'Sí, crear' }).click();
  await page.getByRole('button', { name: 'Cerrar' }).click();
});