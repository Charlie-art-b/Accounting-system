import { test, expect } from '@playwright/test';

test('Crear producto', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Productos' }).click();
  await page.getByRole('link', { name: 'Crear Producto' }).click();
  await page.getByRole('textbox', { name: 'Nombre*' }).click();
  await page.getByRole('textbox', { name: 'Nombre*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre*' }).fill('B');
  await page.getByRole('textbox', { name: 'Nombre*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre*' }).fill('Bicicletas');
  await page.getByRole('textbox', { name: 'Descripción' }).click();
  await page.getByRole('textbox', { name: 'Descripción' }).click();
  await page.getByRole('textbox', { name: 'Descripción' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Descripción' }).fill('5');
  await page.getByRole('textbox', { name: 'Descripción' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Descripción' }).fill('500 paquetes');
  await page.getByRole('button', { name: 'Seleccione una opción' }).click();
  await page.getByText('María Elena Solís Pérez').click();
  await page.getByRole('button', { name: 'Crear' }).click();
  await page.getByRole('button', { name: 'Sí, crear' }).click();
  await page.locator('a:nth-child(2)').click();
});