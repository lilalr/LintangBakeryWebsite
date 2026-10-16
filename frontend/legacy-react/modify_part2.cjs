const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

function replaceExact(search, replace) {
  if (!code.includes(search)) {
    console.error("COULD NOT FIND:\n" + search.substring(0, 100) + "...");
  } else {
    code = code.replace(search, replace);
  }
}

// 1. Pass stocks to AdminProducts in App render
replaceExact(
  '{page === "admin-products" && <AdminProducts />}',
  '{page === "admin-products" && <AdminProducts stocks={stocks} setStocks={setStocks} />}'
);

// 2. Lift products state in App
replaceExact(
  '  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);',
  `  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);\n  const [products, setProducts] = useState<Product[]>(() => { try { return JSON.parse(localStorage.getItem("lintang_products") || JSON.stringify(PRODUCTS)); } catch { return PRODUCTS; } });\n  useEffect(() => { localStorage.setItem("lintang_products", JSON.stringify(products)); }, [products]);`
);

// Pass products to HomePage, CatalogPage, and AdminProducts
replaceExact(
  '{page === "home" && <HomePage setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}',
  '{page === "home" && <HomePage products={products} setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}'
);
replaceExact(
  '{page === "catalog" && <CatalogPage setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}',
  '{page === "catalog" && <CatalogPage products={products} setPage={setPage} setSelectedProduct={setSelectedProduct} addToCart={addToCart} isLoggedIn={isLoggedIn} />}'
);
replaceExact(
  '{page === "admin-products" && <AdminProducts stocks={stocks} setStocks={setStocks} />}',
  '{page === "admin-products" && <AdminProducts products={products} setProducts={setProducts} stocks={stocks} setStocks={setStocks} />}'
);

// Change HomePage signature
replaceExact(
  'function HomePage({ setPage, setSelectedProduct, addToCart, isLoggedIn }: { setPage: (p: Page) => void; setSelectedProduct: (p: Product) => void; addToCart: (p: Product, q: number) => void; isLoggedIn: boolean }) {',
  'function HomePage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[]; setPage: (p: Page) => void; setSelectedProduct: (p: Product) => void; addToCart: (p: Product, q: number) => void; isLoggedIn: boolean }) {'
);
// Change CatalogPage signature
replaceExact(
  'function CatalogPage({ setPage, setSelectedProduct, addToCart, isLoggedIn }: { setPage: (p: Page) => void; setSelectedProduct: (p: Product) => void; addToCart: (p: Product, q: number) => void; isLoggedIn: boolean }) {',
  'function CatalogPage({ products, setPage, setSelectedProduct, addToCart, isLoggedIn }: { products: Product[]; setPage: (p: Page) => void; setSelectedProduct: (p: Product) => void; addToCart: (p: Product, q: number) => void; isLoggedIn: boolean }) {'
);

// Use products instead of PRODUCTS in HomePage and CatalogPage
replaceExact(
  '        {PRODUCTS.slice(0, 3).map(p => (',
  '        {products.filter(p => p.salesChannel !== "offline").slice(0, 3).map(p => ('
);
replaceExact(
  '      const matchesCat = filter === "Semua" || p.category === filter;\n      return matchesSearch && matchesCat;\n    })',
  '      const matchesCat = filter === "Semua" || p.category === filter;\n      return matchesSearch && matchesCat && p.salesChannel !== "offline";\n    })'
);

// In CatalogPage, PRODUCTS.map -> products.filter(p => p.salesChannel !== "offline").map
replaceExact(
  '  const filtered = PRODUCTS.filter(p => {',
  '  const filtered = products.filter(p => {'
);


// 3. AdminProducts modifications
replaceExact(
  'function AdminProducts() {\n  const [products, setProducts] = useState<Product[]>(PRODUCTS);',
  'function AdminProducts({ products, setProducts, stocks, setStocks }: { products: Product[]; setProducts: React.Dispatch<React.SetStateAction<Product[]>>; stocks: StockItem[]; setStocks: React.Dispatch<React.SetStateAction<StockItem[]>> }) {'
);
replaceExact(
  '  const [fotoUrl, setFotoUrl] = useState("");',
  '  const [fotoUrl, setFotoUrl] = useState("");\n  const [salesChannel, setSalesChannel] = useState<"online" | "offline">("online");'
);
replaceExact(
  'const openAdd = () => { setEditing(null); setNama(""); setDeskripsi(""); setHarga(""); setFotoUrl(""); setShowForm(true); };',
  'const openAdd = () => { setEditing(null); setNama(""); setDeskripsi(""); setHarga(""); setFotoUrl(""); setSalesChannel("online"); setShowForm(true); };'
);
replaceExact(
  'const openEdit = (p: Product) => { setEditing(p); setNama(p.name); setDeskripsi(p.description); setHarga(String(p.pricePerBal)); setFotoUrl(p.image); setShowForm(true); };',
  'const openEdit = (p: Product) => { setEditing(p); setNama(p.name); setDeskripsi(p.description); setHarga(String(p.pricePerBal)); setFotoUrl(p.image); setSalesChannel(p.salesChannel || "online"); setShowForm(true); };'
);

replaceExact(
  '      setProducts(prev => prev.map(p => p.id === editing.id ? { ...p, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage } : p));',
  '      setProducts(prev => prev.map(p => p.id === editing.id ? { ...p, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, salesChannel } : p));\n      if (salesChannel === "offline" && !stocks.some(s => s.id === editing.id)) {\n        setStocks(prev => [...prev, { id: editing.id, name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", stock: 0 }]);\n      }'
);
replaceExact(
  '      const newP: Product = { id: Date.now(), name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering" };',
  '      const newP: Product = { id: Date.now(), name: nama, description: deskripsi, pricePerBal: parseInt(harga), image: finalImage, category: "Kue Kering", salesChannel };\n      if (salesChannel === "offline") {\n        setStocks(prev => [...prev, { ...newP, stock: 0 }]);\n      }'
);

// Add radio buttons for salesChannel in AdminProducts form
replaceExact(
  '              <Input label="Harga per Bal (Rp)" type="number" value={harga} onChange={setHarga} placeholder="85000" required />',
  `              <Input label="Harga per Bal (Rp)" type="number" value={harga} onChange={setHarga} placeholder="85000" required />
              <div className="flex flex-col gap-1.5">
                <label className="text-sm font-medium text-foreground">Kategori Penjualan</label>
                <div className="flex gap-4 mt-1">
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "online"} onChange={() => setSalesChannel("online")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Online (Katalog)</span>
                  </label>
                  <label className="flex items-center gap-2 cursor-pointer">
                    <input type="radio" checked={salesChannel === "offline"} onChange={() => setSalesChannel("offline")} className="w-4 h-4 text-primary" />
                    <span className="text-sm">Offline (Toko Fisik)</span>
                  </label>
                </div>
              </div>`
);

fs.writeFileSync(file, code);
console.log("Modifications 2 done.");
