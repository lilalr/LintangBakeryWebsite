const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

const brokenPart = `      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Stok Offline</h2>
          <p className="text-sm text-muted-foreground mt-0.5">Stok produk toko offline</p>
        </div>
        
      </div>

      ;
  useEffect(() => { localStorage.setItem("lintang_products", JSON.stringify(products)); }, [products]);`;

const restoredPart = `      <div className="flex items-center justify-between mb-6">
        <div>
          <h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "'Playfair Display', serif" }}>Stok Offline</h2>
          <p className="text-sm text-muted-foreground mt-0.5">Stok produk toko offline</p>
        </div>
      </div>

      {stocks.some(s => s.stock < 50) && (
        <div className="bg-amber-50 border border-amber-200 rounded-2xl p-4 mb-6 flex items-start gap-3">
          <AlertTriangle className="w-5 h-5 text-amber-600 mt-0.5 flex-shrink-0" />
          <div>
            <p className="font-semibold text-amber-800 text-sm">Peringatan Stok Rendah</p>
            <p className="text-sm text-amber-700 mt-0.5">
              {stocks.filter(s => s.stock < 50).map(s => s.name).join(", ")} — stok di bawah 50 bal.
            </p>
          </div>
        </div>
      )}

      <div className="bg-card border border-border rounded-2xl overflow-hidden">
        <div className="overflow-x-auto">
          <table className="w-full text-sm">
            <thead>
              <tr className="border-b border-border bg-muted/30">
                {["Produk", "Stok Saat Ini", "Status", "Penyesuaian", "Aksi"].map(h => (
                  <th key={h} className="text-left px-4 py-3 text-xs font-semibold text-muted-foreground uppercase tracking-wide">{h}</th>
                ))}
              </tr>
            </thead>
            <tbody>
              {stocks.map((s, i) => (
                <tr key={s.id} className={\`border-b border-border last:border-0 \${i % 2 === 0 ? "" : "bg-muted/10"}\`}>
                  <td className="px-4 py-4">
                    <div className="flex items-center gap-3">
                      <img src={s.image} alt={s.name} className="w-10 h-10 rounded-xl object-cover bg-muted flex-shrink-0" />
                      <div>
                        <p className="font-medium text-foreground">{s.name}</p>
                        <p className="text-xs text-muted-foreground">{s.category}</p>
                      </div>
                    </div>
                  </td>
                  <td className="px-4 py-4">
                    <span className="text-xl font-bold text-foreground">{s.stock}</span>
                    <span className="text-xs text-muted-foreground ml-1">bal</span>
                  </td>
                  <td className="px-4 py-4">
                    {s.stock < 50 ? (
                      <span className="inline-flex items-center gap-1 bg-red-100 text-red-700 border border-red-200 px-2.5 py-1 rounded-full text-xs font-medium">
                        <AlertTriangle className="w-3 h-3" /> Rendah
                      </span>
                    ) : s.stock < 100 ? (
                      <span className="inline-flex items-center gap-1 bg-amber-100 text-amber-700 border border-amber-200 px-2.5 py-1 rounded-full text-xs font-medium">
                        Sedang
                      </span>
                    ) : (
                      <span className="inline-flex items-center gap-1 bg-green-100 text-green-700 border border-green-200 px-2.5 py-1 rounded-full text-xs font-medium">
                        <Check className="w-3 h-3" /> Cukup
                      </span>
                    )}
                  </td>
                  <td className="px-4 py-4">
                    <div className="flex items-center gap-2">
                      <button onClick={() => { const v = Math.max(0, (parseInt(adjustments[s.id] || "0") || 0) - 1); updateAdj(s.id, v === 0 ? "" : String(v)); }}
                        className="w-7 h-7 rounded-lg border border-border flex items-center justify-center hover:bg-muted cursor-pointer">
                        <Minus className="w-3 h-3" />
                      </button>
                      <input type="number" value={adjustments[s.id] ?? ""} onChange={e => updateAdj(s.id, e.target.value)} placeholder="0" min={0}
                        className="w-16 text-center px-2 py-1.5 rounded-lg border border-border bg-input-background text-sm focus:outline-none focus:ring-2 focus:ring-ring/40" />
                      <button onClick={() => updateAdj(s.id, String((parseInt(adjustments[s.id] || "0") || 0) + 1))}
                        className="w-7 h-7 rounded-lg border border-border flex items-center justify-center hover:bg-muted cursor-pointer">
                        <Plus className="w-3 h-3" />
                      </button>
                    </div>
                  </td>
                  <td className="px-4 py-4">
                    <button onClick={() => applyAdd(s.id)}
                      className={\`px-3 py-1.5 text-xs font-medium rounded-lg border transition-colors cursor-pointer \${saved === s.id ? "bg-primary/10 text-primary border-primary/20" : "bg-primary/10 text-primary border-primary/20 hover:bg-primary/20"}\`}>
                      {saved === s.id ? <><Check className="w-3 h-3 inline mr-1" />Tersimpan</> : "+ Tambah Stok"}
                    </button>
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>
      </div>
    </div>
  );
}

// ─── App Root ─────────────────────────────────────────────────────────────────
export default function App() {
  const [page, setPage] = useState<Page>("home");
  const [selectedProduct, setSelectedProduct] = useState<Product | null>(null);
  const [products, setProducts] = useState<Product[]>(() => { try { return JSON.parse(localStorage.getItem("lintang_products") || JSON.stringify(PRODUCTS)); } catch { return PRODUCTS; } });
  useEffect(() => { localStorage.setItem("lintang_products", JSON.stringify(products)); }, [products]);`;

code = code.replace(brokenPart, restoredPart);
fs.writeFileSync(file, code);
console.log("Restored App.tsx");
