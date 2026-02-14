import { test, expect } from '@playwright/test';

test('Eliminar producto', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Productos' }).click();
  await page.getByRole('checkbox', { name: 'Seleccionar/deseleccionar el elemento 1 para las acciones masivas.' }).check();
  await page.getByRole('button', { name: 'Abrir acciones' }).click();
  await page.getByRole('button', { name: 'Borrar seleccionados' }).click();
  await page.getByRole('button', { name: 'Borrar', exact: true }).click();
  await page.getByRole('status').getByRole('button').click();
});