import { test, expect } from '@playwright/test';

test('crear inventario,validar espacios en blanco y advertencias', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Inventarios' }).click();
  await page.getByRole('link', { name: 'Crear Inventario' }).click();
  await page.getByRole('button', { name: 'Crear' }).click();
  await page.getByRole('button', { name: 'Sí, crear' }).click();
  await page.getByRole('button', { name: 'Cerrar' }).click();
  await page.getByRole('button', { name: 'Seleccione una opción' }).click();
  await page.getByRole('option', { name: 'Carlos' }).click();
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).click();
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).fill('C');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).fill('');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).fill('I');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).press('CapsLock');
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).fill('Inventario pss');
  await page.getByRole('button', { name: 'Crear' }).click();
  await page.getByRole('button', { name: 'Sí, crear' }).click();
});