#!/usr/bin/env node
// Simple Puppeteer PDF renderer
// Usage: node puppeteer-render.js <url> <outputPath>
const fs = require('fs');
const path = require('path');
const puppeteer = require('puppeteer');

async function render(url, outPath) {
  const browser = await puppeteer.launch({ args: ['--no-sandbox','--disable-setuid-sandbox'] });
  try {
    const page = await browser.newPage();
    await page.goto(url, { waitUntil: 'networkidle0' });
    // Give any fonts a moment
    await page.evaluate(() => new Promise(resolve => setTimeout(resolve, 250)));
    await page.pdf({ path: outPath, format: 'A4', printBackground: true, margin: { top: '18mm', bottom: '18mm', left: '18mm', right: '18mm' } });
  } finally {
    await browser.close();
  }
}

async function main(){
  const argv = process.argv.slice(2);
  if (argv.length < 2) {
    console.error('Usage: node puppeteer-render.js <url> <outputPath>');
    process.exit(2);
  }
  const [url, outPath] = argv;
  try {
    await render(url, outPath);
    console.log('OK', outPath);
    process.exit(0);
  } catch (err) {
    console.error('ERROR', err && err.message || err);
    process.exit(1);
  }
}

main();
