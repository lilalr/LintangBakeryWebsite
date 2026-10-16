const fs = require('fs');
const file = 'src/app/App.tsx';
let code = fs.readFileSync(file, 'utf8');

const adminStockRegex = /function AdminStock.*?\s+const \[adjustments, setAdjustments\] = useState<Record<number, string>>\(\{\}\);\s+const \[saved, setSaved\] = useState<number \| null>\(null\);[\s\S]*?const applyAdd = \(id: number\) => \{[\s\S]*?setSaved\(null\), 2000\);\n  \};\n\n  const saveNewProduct = \(\) => \{[\s\S]*?setShowNewForm\(false\);\n  \};\n\n  return \(/;

const newAdminStockStart = `function AdminStock({ stocks, setStocks }: { stocks: StockItem[]; setStocks: React.Dispatch<React.SetStateAction<StockItem[]>> }) {
  const [adjustments, setAdjustments] = useState<Record<number, string>>({});
  const [saved, setSaved] = useState<number | null>(null);

  const updateAdj = (id: number, val: string) => setAdjustments(prev => ({ ...prev, [id]: val }));

  const applyAdd = (id: number) => {
    const adj = parseInt(adjustments[id] || "0") || 0;
    if (adj <= 0) return;
    setStocks(prev => prev.map(s => s.id === id ? { ...s, stock: s.stock + adj } : s));
    setAdjustments(prev => ({ ...prev, [id]: "0" }));
    setSaved(id);
    setTimeout(() => setSaved(null), 2000);
  };

  return (`

code = code.replace(adminStockRegex, newAdminStockStart);

const buttonRegex = /<Btn size="sm" onClick=\{\(\) => setShowNewForm\(true\)\}>[\s\S]*?<\/Btn>/;
code = code.replace(buttonRegex, '');

const formRegex = /\{showNewForm && \([\s\S]*?\}\)/;
code = code.replace(formRegex, '');

fs.writeFileSync(file, code);
console.log("Modifications 2 stok done.");
