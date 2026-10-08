const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

// 1. Fix HomePage signature
code = code.replace(
  /function HomePage\(\{ setPage, setSelectedProduct, addToCart, isLoggedIn \}: \{/g,
  'function HomePage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];'
);

// 2. Fix CatalogPage signature
code = code.replace(
  /function CatalogPage\(\{ setPage, setSelectedProduct, addToCart, isLoggedIn \}: \{/g,
  'function CatalogPage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[];'
);

// 3. Fix PRODUCTS usage in HomePage
code = code.replace(
  /\{PRODUCTS\.slice\(0, 3\)\.map\(p => \(/g,
  '{products.filter(p => p.salesChannel !== "offline").slice(0, 3).map(p => ('
);

// 4. Fix PRODUCTS usage in CatalogPage
code = code.replace(
  /const filtered = PRODUCTS\.filter\(p => \{/g,
  'const filtered = products.filter(p => p.salesChannel !== "offline").filter(p => {'
);

fs.writeFileSync(file, code);
console.log("Modifications 2 fix done.");
