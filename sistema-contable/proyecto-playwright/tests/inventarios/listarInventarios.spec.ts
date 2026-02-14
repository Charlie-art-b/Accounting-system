import { test, expect } from '@playwright/test';

test('listar y filtrar inventarios', async ({ page }) => {
  await page.goto('http://127.0.0.1:8000/admin');
  await page.getByRole('link', { name: 'Inventarios' }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).click();
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('I');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('Inventario ');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('Inventario P');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).press('CapsLock');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('Inventario de pro');
  await page.goto('http://127.0.0.1:8000/admin/inventories?search=Inventario+de+pro');
  await page.getByRole('searchbox', { name: 'Búsqueda', exact: true }).fill('Inventario de prove');
});