import { test, expect } from '@playwright/test';

test('test', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Clientes' }).click();
  await page.getByRole('link', { name: 'Editar' }).first().click();
  await page.getByRole('switch', { name: 'Estado*' }).click();
  await page.getByRole('button', { name: 'Guardar cambios' }).click();
  await page.getByRole('button', { name: 'Sí, guardar' }).click();
  await page.getByRole('link', { name: 'Cancelar' }).click();
});