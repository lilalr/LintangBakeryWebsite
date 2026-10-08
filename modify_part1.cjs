const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

function replaceExact(search, replace) {
  if (!code.includes(search)) {
    console.error("COULD NOT FIND:", search.substring(0, 50) + "...");
  } else {
    code = code.replace(search, replace);
  }
}

// 1. Rename menu & tombol
replaceExact(
  '{ icon: <ShoppingBag className="w-4 h-4" />, label: "Kasir", target: "admin-transactions" as Page },',
  '{ icon: <ShoppingBag className="w-4 h-4" />, label: "Transaksi Offline", target: "admin-transactions" as Page },'
);
replaceExact(
  '{ icon: <Archive className="w-4 h-4" />, label: "Stok", target: "admin-stock" as Page },',
  '{ icon: <Archive className="w-4 h-4" />, label: "Stok Offline", target: "admin-stock" as Page },'
);
replaceExact(
  '<h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Kasir Toko Offline</h2>',
  '<h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Transaksi Offline</h2>'
);
replaceExact(
  '<Btn size="sm" onClick={() => setShowForm(true)}><Plus className="w-4 h-4" /> Catat Kasir</Btn>',
  '<Btn size="sm" onClick={() => setShowForm(true)}><Plus className="w-4 h-4" /> Tambah Transaksi</Btn>'
);
replaceExact(
  '<h3 className="font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Catat Kasir Offline</h3>',
  '<h3 className="font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Tambah Transaksi</h3>'
);
replaceExact(
  '<h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Manajemen Stok</h2>',
  '<h2 className="text-xl font-bold text-foreground" style={{ fontFamily: "\'Playfair Display\', serif" }}>Stok Offline</h2>'
);
replaceExact(
  '<h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "\'Playfair Display\', serif" }}>Kasir Toko Offline</h3>',
  '<h3 className="font-semibold text-foreground text-sm" style={{ fontFamily: "\'Playfair Display\', serif" }}>Transaksi Offline</h3>'
);

// 2. Add salesChannel to Product
replaceExact(
  '  category: string;\n}',
  '  category: string;\n  salesChannel?: "online" | "offline";\n}'
);

fs.writeFileSync(file, code);
console.log("Modifications 1 & 2 (partial) done.");
