import { test, expect } from '@playwright/test';

test('Listar productos', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Productos' }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('LA');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('Laptop');
});