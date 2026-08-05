import React, { useState } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Button } from '../components/ui/button';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { Textarea } from '../components/ui/textarea';
import { Popover, PopoverContent, PopoverTrigger } from '../components/ui/popover';
import { Calendar } from '../components/ui/calendar';
import { toast } from 'sonner';
import { Bell, Search, ScanBarcode, Plus, CalendarIcon, Printer } from 'lucide-react';
import { Sale, SaleItem } from '../data/mockData';
import { format } from 'date-fns';

export const POSPage: React.FC = () => {
  const { products, addSale, categories } = useInventory();
  const { user } = useAuth();
  
  const [cart, setCart] = useState<SaleItem[]>([]);
  const [customerName, setCustomerName] = useState<string>('');
  const [paymentMethod, setPaymentMethod] = useState<'cash' | 'card' | 'e-wallet' | 'bon'>('cash');
  const [deliveryOption, setDeliveryOption] = useState<'pickup' | 'delivery'>('pickup');
  const [deliveryFee, setDeliveryFee] = useState<number>(0);
  const [orderNotes, setOrderNotes] = useState<string>('');
  const [searchQuery, setSearchQuery] = useState<string>('');
  const [selectedCategory, setSelectedCategory] = useState<string>('all');
  const [discount, setDiscount] = useState<number>(0);
  const [tax, setTax] = useState<number>(11); // PPN 11%
  const [transactionDate, setTransactionDate] = useState<Date>(new Date());
  const [lastSaleNumber, setLastSaleNumber] = useState<string>('');

  const addToCart = (productId: string) => {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    if (product.current_stock <= 0) {
      toast.error('Stok habis');
      return;
    }

    const existingItem = cart.find(item => item.product_id === productId);
    
    if (existingItem) {
      const newQuantity = existingItem.quantity + 1;
      if (newQuantity > product.current_stock) {
        toast.error(`Stok tidak mencukupi. Tersedia: ${product.current_stock}`);
        return;
      }
      
      setCart(cart.map(item =>
        item.product_id === productId
          ? { ...item, quantity: newQuantity, total: newQuantity * item.unit_price }
          : item
      ));
    } else {
      const newItem: SaleItem = {
        product_id: productId,
        quantity: 1,
        unit_price: product.selling_price,
        total: product.selling_price,
      };
      setCart([...cart, newItem]);
    }

    toast.success('Ditambahkan ke keranjang');
  };

  const updateCartQuantity = (productId: string, newQuantity: number) => {
    const product = products.find(p => p.id === productId);
    if (!product) return;

    if (newQuantity > product.current_stock) {
      toast.error(`Maksimal: ${product.current_stock}`);
      return;
    }

    if (newQuantity <= 0) {
      setCart(cart.filter(item => item.product_id !== productId));
      return;
    }

    setCart(cart.map(item =>
      item.product_id === productId
        ? { ...item, quantity: newQuantity, total: newQuantity * item.unit_price }
        : item
    ));
  };

  const calculateSubtotal = () => {
    return cart.reduce((sum, item) => sum + item.total, 0);
  };

  const calculateDiscount = () => {
    return (calculateSubtotal() * discount) / 100;
  };

  const calculateTax = () => {
    return ((calculateSubtotal() - calculateDiscount()) * tax) / 100;
  };

  const calculateTotal = () => {
    return calculateSubtotal() - calculateDiscount() + calculateTax() + deliveryFee;
  };

  const handleCheckout = () => {
    if (cart.length === 0) {
      toast.error('Keranjang kosong');
      return;
    }

    const saleNumber = `INV-${transactionDate.getFullYear()}-${String(Math.floor(Math.random() * 10000)).padStart(4, '0')}`;
    setLastSaleNumber(saleNumber);
    
    const newSale: Omit<Sale, 'id' | 'created_at'> = {
      sale_number: saleNumber,
      total_amount: calculateTotal(),
      payment_method: paymentMethod === 'cash' ? 'cash' : paymentMethod === 'card' ? 'transfer' : 'credit',
      payment_status: paymentMethod === 'bon' ? 'pending' : 'paid',
      items: cart,
      customer_name: customerName || undefined,
      created_by: user?.id || 'unknown',
    };

    addSale(newSale);
    
    toast.success(`Transaksi berhasil! Invoice: ${saleNumber}`);
    
    // Auto print receipt
    setTimeout(() => {
      handlePrintReceipt(saleNumber);
    }, 500);
    
    // Reset form
    setCart([]);
    setCustomerName('');
    setPaymentMethod('cash');
    setDeliveryOption('pickup');
    setOrderNotes('');
    setDiscount(0);
    setDeliveryFee(0);
    setTransactionDate(new Date());
  };

  const handlePrintReceipt = (invoiceNumber?: string) => {
    const printWindow = window.open('', '', 'width=300,height=600');
    if (!printWindow) {
      toast.error('Tidak dapat membuka jendela cetak');
      return;
    }

    const receiptDate = format(transactionDate, 'dd/MM/yyyy HH:mm');
    const usedInvoice = invoiceNumber || lastSaleNumber;

    const receiptHTML = `
      <!DOCTYPE html>
      <html>
      <head>
        <title>Struk - ${usedInvoice}</title>
        <style>
          body {
            font-family: 'Courier New', monospace;
            font-size: 12px;
            margin: 0;
            padding: 10px;
            width: 280px;
          }
          .center { text-align: center; }
          .bold { font-weight: bold; }
          .line { border-top: 1px dashed #000; margin: 5px 0; }
          .row { display: flex; justify-content: space-between; margin: 2px 0; }
          .header { font-size: 16px; font-weight: bold; margin-bottom: 5px; }
          table { width: 100%; border-collapse: collapse; }
          td { padding: 2px 0; }
          .item-name { width: 60%; }
          .item-qty { width: 15%; text-align: center; }
          .item-price { width: 25%; text-align: right; }
        </style>
      </head>
      <body>
        <div class="center header">TOKO BANGUNAN</div>
        <div class="center">Jl. Contoh No. 123</div>
        <div class="center">Telp: 0812-3456-7890</div>
        <div class="line"></div>
        <div class="row">
          <span>No Invoice:</span>
          <span class="bold">${usedInvoice}</span>
        </div>
        <div class="row">
          <span>Tanggal:</span>
          <span>${receiptDate}</span>
        </div>
        <div class="row">
          <span>Kasir:</span>
          <span>${user?.name || 'Admin'}</span>
        </div>
        ${customerName ? `
        <div class="row">
          <span>Pelanggan:</span>
          <span>${customerName}</span>
        </div>
        ` : ''}
        <div class="line"></div>
        
        <table>
          <thead>
            <tr>
              <td class="item-name bold">Item</td>
              <td class="item-qty bold">Qty</td>
              <td class="item-price bold">Harga</td>
            </tr>
          </thead>
          <tbody>
            ${cart.map(item => {
              const product = products.find(p => p.id === item.product_id);
              return `
                <tr>
                  <td class="item-name">${product?.name || 'Unknown'}</td>
                  <td class="item-qty">${item.quantity}</td>
                  <td class="item-price">${item.total.toLocaleString()}</td>
                </tr>
              `;
            }).join('')}
          </tbody>
        </table>
        
        <div class="line"></div>
        <div class="row">
          <span>Subtotal:</span>
          <span>Rp ${calculateSubtotal().toLocaleString()}</span>
        </div>
        ${discount > 0 ? `
        <div class="row">
          <span>Diskon (${discount}%):</span>
          <span>-Rp ${calculateDiscount().toLocaleString()}</span>
        </div>
        ` : ''}
        <div class="row">
          <span>PPN (${tax}%):</span>
          <span>Rp ${calculateTax().toLocaleString()}</span>
        </div>
        ${deliveryFee > 0 ? `
        <div class="row">
          <span>Biaya Antar:</span>
          <span>Rp ${deliveryFee.toLocaleString()}</span>
        </div>
        ` : ''}
        <div class="line"></div>
        <div class="row bold" style="font-size: 14px;">
          <span>TOTAL:</span>
          <span>Rp ${calculateTotal().toLocaleString()}</span>
        </div>
        <div class="line"></div>
        <div class="row">
          <span>Metode Bayar:</span>
          <span class="bold">${paymentMethod.toUpperCase()}</span>
        </div>
        ${deliveryOption === 'delivery' ? `
        <div class="row">
          <span>Pengiriman:</span>
          <span class="bold">ANTAR</span>
        </div>
        ` : ''}
        <div class="line"></div>
        <div class="center" style="margin-top: 10px;">Terima Kasih!</div>
        <div class="center">Barang yang sudah dibeli</div>
        <div class="center">tidak dapat dikembalikan</div>
        
        <script>
          window.onload = function() {
            window.print();
            setTimeout(function() {
              window.close();
            }, 100);
          }
        </script>
      </body>
      </html>
    `;

    printWindow.document.write(receiptHTML);
    printWindow.document.close();
  };

  const handleHoldOrder = () => {
    if (cart.length === 0) {
      toast.error('Keranjang kosong');
      return;
    }
    toast.info('Pesanan ditahan');
  };

  const handlePrintQuote = () => {
    if (cart.length === 0) {
      toast.error('Keranjang kosong');
      return;
    }
    toast.info('Mencetak quotation...');
  };

  const filteredProducts = products.filter(product => {
    const matchesSearch = product.name.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesCategory = selectedCategory === 'all' || product.category === selectedCategory;
    return matchesSearch && matchesCategory;
  });

  return (
    <div className="h-[calc(100vh-4rem)] flex flex-col bg-gray-50">
      {/* Header */}
      <div className="bg-white border-b px-6 py-4 flex items-center justify-between">
        <h1 className="text-xl font-semibold">Kasir</h1>
        <Button variant="ghost" size="icon">
          <Bell className="w-5 h-5" />
        </Button>
      </div>

      {/* Search Bar */}
      <div className="bg-white border-b px-6 py-4">
        <div className="flex gap-3">
          <div className="flex-1 relative">
            <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" />
            <Input
              placeholder="Cari Barang"
              value={searchQuery}
              onChange={(e) => setSearchQuery(e.target.value)}
              className="pl-10"
            />
          </div>
          <Select value={selectedCategory} onValueChange={setSelectedCategory}>
            <SelectTrigger className="w-48">
              <SelectValue placeholder="Semua Kategori" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua Kategori</SelectItem>
              {categories.map(cat => (
                <SelectItem key={cat.id} value={cat.name}>
                  {cat.name}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
          <Button variant="outline">
            <ScanBarcode className="w-4 h-4 mr-2" />
            Scan Barcode
          </Button>
        </div>
      </div>

      {/* Main Content */}
      <div className="flex-1 grid grid-cols-[1fr_400px] overflow-hidden">
        {/* Left: Product Grid */}
        <div className="overflow-y-auto p-6">
          <div className="grid grid-cols-4 gap-4">
            {filteredProducts.map(product => (
              <div key={product.id} className="bg-white rounded-lg border overflow-hidden">
                {/* Product Image */}
                <div className="aspect-square bg-gray-100 flex items-center justify-center">
                  <span className="text-gray-400 text-sm">No Image</span>
                </div>
                
                {/* Product Info */}
                <div className="p-3 space-y-2">
                  <h3 className="font-medium text-sm line-clamp-2 min-h-[2.5rem]">{product.name}</h3>
                  <div className="space-y-1">
                    <p className="text-xs text-gray-500">
                      Rp {(product.cost_price || 0).toLocaleString()}/Barang
                    </p>
                    <p className="text-sm font-semibold text-blue-600">
                      Rp {(product.selling_price || 0).toLocaleString()}/{product.base_unit || 'pcs'}
                    </p>
                  </div>
                  
                  <div className="flex gap-2">
                    <Button
                      variant="outline"
                      size="sm"
                      className="flex-1 text-xs"
                      disabled
                    >
                      Stok: {product.current_stock || 0}
                    </Button>
                    <Button
                      size="sm"
                      className="bg-green-500 hover:bg-green-600 text-white"
                      onClick={() => addToCart(product.id)}
                      disabled={!product.current_stock || product.current_stock <= 0}
                    >
                      <Plus className="w-4 h-4 mr-1" />
                      Add
                    </Button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>

        {/* Right: Checkout Panel */}
        <div className="bg-white border-l flex flex-col overflow-hidden">
          <div className="flex-1 overflow-y-auto p-6 space-y-4">
            {/* Customer */}
            <div>
              <Label className="text-sm font-medium mb-2 block">Pelanggan:</Label>
              <div className="flex gap-2">
                <Input
                  placeholder="Nama Pelanggan..."
                  value={customerName}
                  onChange={(e) => setCustomerName(e.target.value)}
                  className="flex-1"
                />
                <Button variant="outline" className="bg-pink-500 text-white hover:bg-pink-600">
                  Belum Semua
                </Button>
              </div>
            </div>

            {/* Transaction Date */}
            <div>
              <Label className="text-sm font-medium mb-2 block">Tanggal Transaksi:</Label>
              <Popover>
                <PopoverTrigger asChild>
                  <Button variant="outline" className="w-full justify-start text-left">
                    <CalendarIcon className="mr-2 h-4 w-4" />
                    {format(transactionDate, 'dd MMM yyyy')}
                  </Button>
                </PopoverTrigger>
                <PopoverContent className="w-auto p-0" align="start">
                  <Calendar
                    mode="single"
                    selected={transactionDate}
                    onSelect={(date) => date && setTransactionDate(date)}
                  />
                </PopoverContent>
              </Popover>
            </div>

            {/* List Barang */}
            <div>
              <Label className="text-sm font-medium mb-2 block">List Barang</Label>
              <div className="border rounded-lg overflow-hidden">
                <Table>
                  <TableHeader>
                    <TableRow className="bg-gray-50">
                      <TableHead className="w-12">No</TableHead>
                      <TableHead>Nama Barang</TableHead>
                      <TableHead className="w-20">Jumlah</TableHead>
                      <TableHead className="w-20">Satuan</TableHead>
                      <TableHead className="w-24">Harga</TableHead>
                      <TableHead className="w-24">Subtotal</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {cart.length === 0 ? (
                      <TableRow>
                        <TableCell colSpan={6} className="text-center text-gray-400 py-8">
                          Return ada barang
                        </TableCell>
                      </TableRow>
                    ) : (
                      cart.map((item, index) => {
                        const product = products.find(p => p.id === item.product_id);
                        if (!product) return null;

                        return (
                          <TableRow key={item.product_id}>
                            <TableCell>{index + 1}</TableCell>
                            <TableCell className="font-medium">{product.name}</TableCell>
                            <TableCell>
                              <Input
                                type="number"
                                min="1"
                                value={item.quantity}
                                onChange={(e) => updateCartQuantity(item.product_id, Number(e.target.value))}
                                className="h-8 text-sm"
                              />
                            </TableCell>
                            <TableCell>{product.base_unit}</TableCell>
                            <TableCell>Rp {item.unit_price.toLocaleString()}</TableCell>
                            <TableCell>Rp {item.total.toLocaleString()}</TableCell>
                          </TableRow>
                        );
                      })
                    )}
                  </TableBody>
                </Table>
              </div>
            </div>

            {/* Order Notes */}
            <div>
              <Label className="text-sm font-medium mb-2 block">Catatan Pesanan</Label>
              <Textarea
                placeholder="Tambahkan catatan..."
                value={orderNotes}
                onChange={(e) => setOrderNotes(e.target.value)}
                rows={2}
              />
            </div>

            {/* Delivery Options */}
            <div>
              <Label className="text-sm font-medium mb-2 block">Opsi Pengiriman</Label>
              <div className="flex gap-2">
                <Button
                  variant={deliveryOption === 'pickup' ? 'default' : 'outline'}
                  className={`flex-1 ${deliveryOption === 'pickup' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => {
                    setDeliveryOption('pickup');
                    setDeliveryFee(0);
                  }}
                >
                  Ambil Sendiri
                </Button>
                <Button
                  variant={deliveryOption === 'delivery' ? 'default' : 'outline'}
                  className={`flex-1 ${deliveryOption === 'delivery' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => setDeliveryOption('delivery')}
                >
                  Antar
                </Button>
              </div>
              
              {/* Delivery Fee Input - only show when delivery is selected */}
              {deliveryOption === 'delivery' && (
                <div className="mt-2">
                  <Label className="text-xs text-gray-600 mb-1 block">Biaya Antar (Rp)</Label>
                  <Input
                    type="number"
                    min="0"
                    value={deliveryFee}
                    onChange={(e) => setDeliveryFee(Number(e.target.value))}
                    placeholder="0"
                    className="h-9"
                  />
                </div>
              )}
            </div>

            {/* Payment Method */}
            <div>
              <Label className="text-sm font-medium mb-2 block">Metode Pembayaran</Label>
              <div className="grid grid-cols-4 gap-2">
                <Button
                  variant={paymentMethod === 'cash' ? 'default' : 'outline'}
                  className={`${paymentMethod === 'cash' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => setPaymentMethod('cash')}
                >
                  Cash
                </Button>
                <Button
                  variant={paymentMethod === 'card' ? 'default' : 'outline'}
                  className={`${paymentMethod === 'card' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => setPaymentMethod('card')}
                >
                  Card
                </Button>
                <Button
                  variant={paymentMethod === 'e-wallet' ? 'default' : 'outline'}
                  className={`${paymentMethod === 'e-wallet' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => setPaymentMethod('e-wallet')}
                >
                  E-Wallet
                </Button>
                <Button
                  variant={paymentMethod === 'bon' ? 'default' : 'outline'}
                  className={`${paymentMethod === 'bon' ? 'bg-black text-white hover:bg-black/90' : ''}`}
                  onClick={() => setPaymentMethod('bon')}
                >
                  Bon
                </Button>
              </div>
            </div>

            {/* Summary */}
            <div className="space-y-2 pt-4 border-t">
              <div className="flex justify-between text-sm">
                <span>Subtotal</span>
                <span>Rp {calculateSubtotal().toLocaleString()}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span>Diskon</span>
                <span>Rp {calculateDiscount().toLocaleString()}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span>Pajak / PPN ({tax}%)</span>
                <span>Rp {calculateTax().toLocaleString()}</span>
              </div>
              <div className="flex justify-between text-sm">
                <span>Biaya Pengiriman</span>
                <span>Rp {deliveryFee.toLocaleString()}</span>
              </div>
              <div className="flex justify-between text-lg font-bold pt-2 border-t">
                <span>TOTAL AKHIR</span>
                <span className="text-green-600">Rp {calculateTotal().toLocaleString()}</span>
              </div>
            </div>
          </div>

          {/* Action Buttons */}
          <div className="p-6 border-t bg-white space-y-2">
            <div className="grid grid-cols-3 gap-2">
              <Button
                variant="outline"
                className="bg-pink-100 text-pink-600 hover:bg-pink-200 border-pink-300"
                onClick={handleHoldOrder}
              >
                Hold Order
              </Button>
              <Button
                variant="outline"
                className="bg-blue-100 text-blue-600 hover:bg-blue-200 border-blue-300"
                onClick={handlePrintQuote}
              >
                Print Quote
              </Button>
              <Button
                className="bg-green-500 hover:bg-green-600 text-white"
                onClick={handleCheckout}
              >
                Checkout
              </Button>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
};