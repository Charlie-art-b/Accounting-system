import { test, expect } from '@playwright/test';

test('Editar inventario', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Inventarios' }).click();
  await page.getByRole('link', { name: 'Editar' }).nth(1).click();
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).click();
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).click();
  await page.getByRole('textbox', { name: 'Nombre del inventario*' }).fill('Inventario de canasta basica');
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await page.getByRole('button', { name: 'Sí, guardar' }).click();
  await page.getByRole('status').getByRole('button').click();
});

