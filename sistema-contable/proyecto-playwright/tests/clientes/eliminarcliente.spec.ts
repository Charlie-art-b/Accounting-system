import { test, expect } from '@playwright/test';

test('eliminar cliente', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Clientes' }).click();
  await page.getByRole('cell', { name: 'Seleccionar/deseleccionar el elemento 1 para las acciones masivas.' }).click();
  await page.getByRole('button', { name: 'Abrir acciones' }).click();
  await page.getByRole('button', { name: 'Borrar seleccionados' }).click();
  await page.getByRole('button', { name: 'Borrar', exact: true }).click();
});