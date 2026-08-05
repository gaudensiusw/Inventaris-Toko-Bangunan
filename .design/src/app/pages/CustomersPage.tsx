import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Input } from '../components/ui/input';
import { Label } from '../components/ui/label';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '../components/ui/dialog';
import {
  Users,
  DollarSign,
  AlertTriangle,
  CheckCircle,
  Clock,
  Plus,
  CreditCard,
  Building2,
  Phone,
  Mail,
  MapPin,
  Calendar,
  TrendingUp,
  Filter,
  Search,
  Receipt,
  Banknote,
  ArrowDownUp,
} from 'lucide-react';
import { cn } from '../components/ui/utils';

export const CustomersPage: React.FC = () => {
  const { user } = useAuth();
  const { customers, customerTransactions, products, addCustomerPayment } = useInventory();
  const [filterStatus, setFilterStatus] = useState<'all' | 'unpaid' | 'partial' | 'paid'>('all');
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedCustomer, setSelectedCustomer] = useState<string | null>(null);
  const [selectedTransaction, setSelectedTransaction] = useState<string | null>(null);
  
  // Payment form state
  const [paymentMethod, setPaymentMethod] = useState<'cash' | 'transfer' | 'mixed'>('cash');
  const [cashAmount, setCashAmount] = useState('');
  const [transferAmount, setTransferAmount] = useState('');
  const [bankName, setBankName] = useState('');
  const [referenceNumber, setReferenceNumber] = useState('');
  const [paymentNotes, setPaymentNotes] = useState('');
  const [showPaymentDialog, setShowPaymentDialog] = useState(false);

  // Filter and search customers
  const filteredCustomers = useMemo(() => {
    return customers
      .filter(customer => {
        const matchesSearch =
          customer.name.toLowerCase().includes(searchQuery.toLowerCase()) ||
          customer.customer_code.toLowerCase().includes(searchQuery.toLowerCase()) ||
          customer.phone.includes(searchQuery);
        return matchesSearch;
      })
      .sort((a, b) => b.total_outstanding - a.total_outstanding);
  }, [customers, searchQuery]);

  // Get transactions for selected customer
  const customerTransactionsFiltered = useMemo(() => {
    if (!selectedCustomer) return [];

    return customerTransactions
      .filter(txn => {
        const matchesCustomer = txn.customer_id === selectedCustomer;
        const matchesStatus = filterStatus === 'all' || txn.payment_status === filterStatus;
        return matchesCustomer && matchesStatus;
      })
      .map(txn => {
        const dueDate = new Date(txn.due_date);
        const today = new Date();
        const isOverdue = dueDate < today && txn.payment_status !== 'paid';
        const daysUntilDue = Math.ceil((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

        return {
          ...txn,
          dueDate,
          isOverdue,
          daysUntilDue,
        };
      })
      .sort((a, b) => b.dueDate.getTime() - a.dueDate.getTime());
  }, [customerTransactions, selectedCustomer, filterStatus]);

  // Calculate summary stats
  const summaryStats = useMemo(() => {
    const totalReceivables = customers.reduce((sum, c) => sum + c.total_outstanding, 0);
    const overdueAmount = customerTransactions
      .filter(txn => {
        const dueDate = new Date(txn.due_date);
        const today = new Date();
        return dueDate < today && txn.payment_status !== 'paid';
      })
      .reduce((sum, txn) => sum + txn.remaining_balance, 0);
    
    const unpaidCount = customerTransactions.filter(txn => txn.payment_status === 'unpaid').length;
    const partialCount = customerTransactions.filter(txn => txn.payment_status === 'partial').length;
    const activeCustomers = customers.filter(c => c.status === 'active' && c.total_outstanding > 0).length;

    return { totalReceivables, overdueAmount, unpaidCount, partialCount, activeCustomers };
  }, [customers, customerTransactions]);

  // Handle payment submission
  const handleAddPayment = () => {
    if (!selectedTransaction || !user) return;

    const transaction = customerTransactions.find(t => t.id === selectedTransaction);
    if (!transaction) return;

    let cash = 0;
    let transfer = 0;
    let total = 0;

    if (paymentMethod === 'cash') {
      cash = parseFloat(cashAmount) || 0;
      total = cash;
    } else if (paymentMethod === 'transfer') {
      transfer = parseFloat(transferAmount) || 0;
      total = transfer;
    } else if (paymentMethod === 'mixed') {
      cash = parseFloat(cashAmount) || 0;
      transfer = parseFloat(transferAmount) || 0;
      total = cash + transfer;
    }

    if (total <= 0 || total > transaction.remaining_balance) {
      alert('Jumlah pembayaran tidak valid!');
      return;
    }

    addCustomerPayment(selectedTransaction, {
      payment_date: new Date().toISOString(),
      payment_method: paymentMethod,
      cash_amount: cash,
      transfer_amount: transfer,
      total_amount: total,
      bank_name: paymentMethod !== 'cash' ? bankName : undefined,
      reference_number: paymentMethod !== 'cash' ? referenceNumber : undefined,
      notes: paymentNotes || undefined,
      received_by: user.id,
    });

    // Reset form
    setCashAmount('');
    setTransferAmount('');
    setBankName('');
    setReferenceNumber('');
    setPaymentNotes('');
    setPaymentMethod('cash');
    setShowPaymentDialog(false);
  };

  const selectedCustomerData = customers.find(c => c.id === selectedCustomer);

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('id-ID', {
      day: '2-digit',
      month: 'short',
      year: 'numeric',
    });
  };

  const getCustomerTypeLabel = (type: string) => {
    const types = {
      retail: 'Retail',
      wholesale: 'Grosir',
      project: 'Proyek',
    };
    return types[type as keyof typeof types] || type;
  };

  const getStatusBadge = (status: string) => {
    const config = {
      paid: { label: 'Lunas', className: 'bg-green-500' },
      partial: { label: 'Sebagian', className: 'bg-yellow-500' },
      unpaid: { label: 'Belum Bayar', className: 'bg-red-500' },
    };
    const { label, className } = config[status as keyof typeof config] || { label: status, className: 'bg-gray-500' };
    return <Badge className={cn('text-white', className)}>{label}</Badge>;
  };

  return (
    <div className="p-6 space-y-6">
      {/* Header */}
      <div>
        <h1 className="text-3xl font-bold">Manajemen Pelanggan</h1>
        <p className="text-muted-foreground">
          Kelola piutang dan transaksi kredit pelanggan
        </p>
      </div>

      {/* Summary Cards */}
      <div className="space-y-3">
        {/* Total Piutang */}
        <Card className="p-4">
          <div className="flex items-center justify-between">
            <div className="flex-1">
              <p className="text-sm text-muted-foreground font-medium mb-1">Total Piutang</p>
              <p className="text-2xl font-bold text-blue-600">
                {formatCurrency(summaryStats.totalReceivables)}
              </p>
            </div>
            <DollarSign className="w-12 h-12 text-blue-500 opacity-20" />
          </div>
        </Card>

        {/* Jatuh Tempo */}
        <Card className="p-4">
          <div className="flex items-center justify-between">
            <div className="flex-1">
              <p className="text-sm text-muted-foreground font-medium mb-1">Jatuh Tempo</p>
              <p className="text-2xl font-bold text-red-600">
                {formatCurrency(summaryStats.overdueAmount)}
              </p>
            </div>
            <AlertTriangle className="w-12 h-12 text-red-500 opacity-20" />
          </div>
        </Card>

        {/* 3 Cards Horizontal */}
        <div className="grid grid-cols-3 gap-3">
          {/* Belum Bayar */}
          <Card className="p-4">
            <div className="flex items-center justify-between">
              <div className="flex-1">
                <p className="text-sm text-muted-foreground font-medium mb-1">Belum Bayar</p>
                <div>
                  <p className="text-2xl font-bold">{summaryStats.unpaidCount}</p>
                  <p className="text-xs text-muted-foreground">transaksi</p>
                </div>
              </div>
              <Clock className="w-12 h-12 text-orange-500 opacity-20" />
            </div>
          </Card>

          {/* Bayar Sebagian */}
          <Card className="p-4">
            <div className="flex items-center justify-between">
              <div className="flex-1">
                <p className="text-sm text-muted-foreground font-medium mb-1">Bayar Sebagian</p>
                <div>
                  <p className="text-2xl font-bold">{summaryStats.partialCount}</p>
                  <p className="text-xs text-muted-foreground">transaksi</p>
                </div>
              </div>
              <ArrowDownUp className="w-12 h-12 text-yellow-500 opacity-20" />
            </div>
          </Card>

          {/* Pelanggan Aktif */}
          <Card className="p-4">
            <div className="flex items-center justify-between">
              <div className="flex-1">
                <p className="text-sm text-muted-foreground font-medium mb-1">Pelanggan Aktif</p>
                <div>
                  <p className="text-2xl font-bold">{summaryStats.activeCustomers}</p>
                  <p className="text-xs text-muted-foreground">dengan piutang</p>
                </div>
              </div>
              <Users className="w-12 h-12 text-purple-500 opacity-20" />
            </div>
          </Card>
        </div>
      </div>

      <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {/* Customer List */}
        <Card className="p-6 lg:col-span-1">
          <div className="space-y-4">
            <div className="flex items-center justify-between">
              <h2 className="text-xl font-bold">Daftar Pelanggan</h2>
              <Button size="sm" className="gap-2">
                <Plus className="w-4 h-4" />
                Tambah
              </Button>
            </div>

            {/* Search */}
            <div className="relative">
              <Search className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-muted-foreground" />
              <Input
                placeholder="Cari nama, kode, atau telepon..."
                value={searchQuery}
                onChange={(e) => setSearchQuery(e.target.value)}
                className="pl-10"
              />
            </div>

            {/* Customer List */}
            <div className="space-y-2 max-h-[600px] overflow-y-auto">
              {filteredCustomers.map((customer) => (
                <div
                  key={customer.id}
                  onClick={() => setSelectedCustomer(customer.id)}
                  className={cn(
                    'p-4 rounded-lg border cursor-pointer transition-colors',
                    selectedCustomer === customer.id
                      ? 'border-blue-500 bg-blue-50'
                      : 'hover:bg-gray-50'
                  )}
                >
                  <div className="flex items-start justify-between gap-2">
                    <div className="flex-1 min-w-0">
                      <p className="font-semibold truncate">{customer.name}</p>
                      <p className="text-sm text-muted-foreground">{customer.customer_code}</p>
                      <div className="flex items-center gap-2 mt-2">
                        <Badge variant="outline" className="text-xs">
                          {getCustomerTypeLabel(customer.customer_type)}
                        </Badge>
                        {customer.total_outstanding > 0 && (
                          <span className="text-xs font-semibold text-red-600">
                            {formatCurrency(customer.total_outstanding)}
                          </span>
                        )}
                      </div>
                    </div>
                    {customer.total_outstanding > 0 && (
                      <AlertTriangle className="w-5 h-5 text-red-500 flex-shrink-0" />
                    )}
                  </div>
                </div>
              ))}
            </div>
          </div>
        </Card>

        {/* Transaction Details */}
        <Card className="p-6 lg:col-span-2">
          {selectedCustomerData ? (
            <div className="space-y-6">
              {/* Customer Info */}
              <div className="pb-6 border-b">
                <div className="flex items-start justify-between mb-4">
                  <div>
                    <h2 className="text-2xl font-bold">{selectedCustomerData.name}</h2>
                    <p className="text-muted-foreground">{selectedCustomerData.customer_code}</p>
                  </div>
                  <Badge
                    className={cn(
                      selectedCustomerData.status === 'active'
                        ? 'bg-green-500'
                        : 'bg-gray-500',
                      'text-white'
                    )}
                  >
                    {selectedCustomerData.status === 'active' ? 'Aktif' : 'Tidak Aktif'}
                  </Badge>
                </div>

                <div className="grid grid-cols-2 gap-4 text-sm">
                  <div className="flex items-center gap-2">
                    <Phone className="w-4 h-4 text-muted-foreground" />
                    <span>{selectedCustomerData.phone}</span>
                  </div>
                  {selectedCustomerData.email && (
                    <div className="flex items-center gap-2">
                      <Mail className="w-4 h-4 text-muted-foreground" />
                      <span>{selectedCustomerData.email}</span>
                    </div>
                  )}
                  <div className="flex items-center gap-2 col-span-2">
                    <MapPin className="w-4 h-4 text-muted-foreground" />
                    <span className="text-xs">{selectedCustomerData.address}</span>
                  </div>
                </div>

                <div className="grid grid-cols-3 gap-4 mt-4 p-4 bg-gray-50 rounded-lg">
                  <div>
                    <p className="text-xs text-muted-foreground">Total Piutang</p>
                    <p className="font-bold text-red-600">
                      {formatCurrency(selectedCustomerData.total_outstanding)}
                    </p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Limit Kredit</p>
                    <p className="font-semibold">{formatCurrency(selectedCustomerData.credit_limit)}</p>
                  </div>
                  <div>
                    <p className="text-xs text-muted-foreground">Tenor Bayar</p>
                    <p className="font-semibold">{selectedCustomerData.payment_terms_days} hari</p>
                  </div>
                </div>
              </div>

              {/* Filter Transactions */}
              <div className="flex items-center gap-2">
                <Filter className="w-4 h-4" />
                <span className="text-sm font-medium">Filter:</span>
                <div className="flex gap-2">
                  {(['all', 'unpaid', 'partial', 'paid'] as const).map((status) => (
                    <Button
                      key={status}
                      size="sm"
                      variant={filterStatus === status ? 'default' : 'outline'}
                      onClick={() => setFilterStatus(status)}
                    >
                      {status === 'all'
                        ? 'Semua'
                        : status === 'unpaid'
                        ? 'Belum Bayar'
                        : status === 'partial'
                        ? 'Sebagian'
                        : 'Lunas'}
                    </Button>
                  ))}
                </div>
              </div>

              {/* Transactions List */}
              <div className="space-y-4">
                <h3 className="font-semibold">
                  Riwayat Transaksi ({customerTransactionsFiltered.length})
                </h3>

                {customerTransactionsFiltered.length === 0 ? (
                  <div className="text-center py-12 text-muted-foreground">
                    <Receipt className="w-12 h-12 mx-auto mb-2 opacity-20" />
                    <p>Tidak ada transaksi</p>
                  </div>
                ) : (
                  <div className="space-y-3 max-h-[500px] overflow-y-auto">
                    {customerTransactionsFiltered.map((txn) => (
                      <div
                        key={txn.id}
                        className="p-4 border rounded-lg hover:bg-gray-50 transition-colors"
                      >
                        <div className="flex items-start justify-between mb-3">
                          <div>
                            <div className="flex items-center gap-2">
                              <p className="font-semibold">{txn.transaction_number}</p>
                              {getStatusBadge(txn.payment_status)}
                              {txn.isOverdue && (
                                <Badge className="bg-red-600 text-white">Overdue</Badge>
                              )}
                            </div>
                            <p className="text-sm text-muted-foreground mt-1">
                              {formatDate(txn.transaction_date)}
                            </p>
                          </div>
                          <div className="text-right">
                            <p className="font-bold text-lg">{formatCurrency(txn.total_amount)}</p>
                            {txn.remaining_balance > 0 && (
                              <p className="text-sm text-red-600">
                                Sisa: {formatCurrency(txn.remaining_balance)}
                              </p>
                            )}
                          </div>
                        </div>

                        {/* Transaction Items */}
                        <div className="text-sm space-y-1 mb-3">
                          {txn.items.map((item, idx) => {
                            const product = products.find(p => p.id === item.product_id);
                            return (
                              <div key={idx} className="flex justify-between text-muted-foreground">
                                <span>
                                  {product?.name || 'Unknown'} x {item.quantity}
                                </span>
                                <span>{formatCurrency(item.total)}</span>
                              </div>
                            );
                          })}
                        </div>

                        {/* Due Date */}
                        <div className="flex items-center justify-between text-sm mb-3">
                          <div className="flex items-center gap-2">
                            <Calendar className="w-4 h-4" />
                            <span className="text-muted-foreground">
                              Jatuh tempo: {formatDate(txn.due_date)}
                            </span>
                            {!txn.isOverdue && txn.payment_status !== 'paid' && (
                              <span className="text-orange-600">
                                ({txn.daysUntilDue > 0 ? `${txn.daysUntilDue} hari lagi` : 'Hari ini'})
                              </span>
                            )}
                          </div>
                        </div>

                        {/* Payment History */}
                        {txn.payments.length > 0 && (
                          <div className="border-t pt-3 space-y-2">
                            <p className="text-xs font-semibold text-muted-foreground">
                              Riwayat Pembayaran:
                            </p>
                            {txn.payments.map((payment) => (
                              <div
                                key={payment.id}
                                className="flex items-center justify-between text-sm bg-green-50 p-2 rounded"
                              >
                                <div className="flex items-center gap-2">
                                  {payment.payment_method === 'cash' ? (
                                    <Banknote className="w-4 h-4 text-green-600" />
                                  ) : payment.payment_method === 'transfer' ? (
                                    <CreditCard className="w-4 h-4 text-blue-600" />
                                  ) : (
                                    <ArrowDownUp className="w-4 h-4 text-purple-600" />
                                  )}
                                  <div>
                                    <p className="font-medium">
                                      {formatDate(payment.payment_date)}
                                    </p>
                                    {payment.payment_method === 'mixed' && (
                                      <p className="text-xs text-muted-foreground">
                                        Cash: {formatCurrency(payment.cash_amount)} + Transfer:{' '}
                                        {formatCurrency(payment.transfer_amount)}
                                      </p>
                                    )}
                                    {payment.reference_number && (
                                      <p className="text-xs text-muted-foreground">
                                        Ref: {payment.reference_number}
                                      </p>
                                    )}
                                  </div>
                                </div>
                                <p className="font-semibold text-green-600">
                                  {formatCurrency(payment.total_amount)}
                                </p>
                              </div>
                            ))}
                          </div>
                        )}

                        {/* Add Payment Button */}
                        {txn.payment_status !== 'paid' && (
                          <div className="border-t pt-3">
                            <Dialog open={showPaymentDialog && selectedTransaction === txn.id} onOpenChange={(open) => {
                              setShowPaymentDialog(open);
                              if (open) setSelectedTransaction(txn.id);
                            }}>
                              <DialogTrigger asChild>
                                <Button
                                  size="sm"
                                  className="w-full gap-2"
                                  variant="outline"
                                  onClick={() => {
                                    setSelectedTransaction(txn.id);
                                    setShowPaymentDialog(true);
                                  }}
                                >
                                  <Plus className="w-4 h-4" />
                                  Tambah Pembayaran
                                </Button>
                              </DialogTrigger>
                              <DialogContent>
                                <DialogHeader>
                                  <DialogTitle>Tambah Pembayaran</DialogTitle>
                                  <DialogDescription>
                                    {txn.transaction_number} - Sisa: {formatCurrency(txn.remaining_balance)}
                                  </DialogDescription>
                                </DialogHeader>

                                <div className="space-y-4">
                                  {/* Payment Method */}
                                  <div className="space-y-2">
                                    <Label>Metode Pembayaran</Label>
                                    <div className="flex gap-2">
                                      <Button
                                        type="button"
                                        size="sm"
                                        variant={paymentMethod === 'cash' ? 'default' : 'outline'}
                                        onClick={() => setPaymentMethod('cash')}
                                        className="flex-1"
                                      >
                                        <Banknote className="w-4 h-4 mr-2" />
                                        Cash
                                      </Button>
                                      <Button
                                        type="button"
                                        size="sm"
                                        variant={paymentMethod === 'transfer' ? 'default' : 'outline'}
                                        onClick={() => setPaymentMethod('transfer')}
                                        className="flex-1"
                                      >
                                        <CreditCard className="w-4 h-4 mr-2" />
                                        Transfer
                                      </Button>
                                      <Button
                                        type="button"
                                        size="sm"
                                        variant={paymentMethod === 'mixed' ? 'default' : 'outline'}
                                        onClick={() => setPaymentMethod('mixed')}
                                        className="flex-1"
                                      >
                                        <ArrowDownUp className="w-4 h-4 mr-2" />
                                        Campuran
                                      </Button>
                                    </div>
                                  </div>

                                  {/* Cash Amount */}
                                  {(paymentMethod === 'cash' || paymentMethod === 'mixed') && (
                                    <div className="space-y-2">
                                      <Label>Jumlah Cash</Label>
                                      <Input
                                        type="number"
                                        placeholder="Masukkan jumlah cash"
                                        value={cashAmount}
                                        onChange={(e) => setCashAmount(e.target.value)}
                                      />
                                    </div>
                                  )}

                                  {/* Transfer Amount */}
                                  {(paymentMethod === 'transfer' || paymentMethod === 'mixed') && (
                                    <>
                                      <div className="space-y-2">
                                        <Label>Jumlah Transfer</Label>
                                        <Input
                                          type="number"
                                          placeholder="Masukkan jumlah transfer"
                                          value={transferAmount}
                                          onChange={(e) => setTransferAmount(e.target.value)}
                                        />
                                      </div>
                                      <div className="space-y-2">
                                        <Label>Nama Bank</Label>
                                        <Input
                                          placeholder="e.g., BCA, Mandiri, BRI"
                                          value={bankName}
                                          onChange={(e) => setBankName(e.target.value)}
                                        />
                                      </div>
                                      <div className="space-y-2">
                                        <Label>No. Referensi Transfer</Label>
                                        <Input
                                          placeholder="e.g., TRF-12345"
                                          value={referenceNumber}
                                          onChange={(e) => setReferenceNumber(e.target.value)}
                                        />
                                      </div>
                                    </>
                                  )}

                                  {/* Total Display */}
                                  <div className="p-4 bg-blue-50 rounded-lg">
                                    <p className="text-sm text-muted-foreground mb-1">Total Pembayaran</p>
                                    <p className="text-2xl font-bold text-blue-600">
                                      {formatCurrency(
                                        (paymentMethod === 'cash' ? parseFloat(cashAmount) || 0 : 0) +
                                        (paymentMethod === 'transfer' ? parseFloat(transferAmount) || 0 : 0) +
                                        (paymentMethod === 'mixed' ? (parseFloat(cashAmount) || 0) + (parseFloat(transferAmount) || 0) : 0)
                                      )}
                                    </p>
                                  </div>

                                  {/* Notes */}
                                  <div className="space-y-2">
                                    <Label>Catatan (Opsional)</Label>
                                    <Input
                                      placeholder="Catatan tambahan"
                                      value={paymentNotes}
                                      onChange={(e) => setPaymentNotes(e.target.value)}
                                    />
                                  </div>

                                  {/* Submit */}
                                  <div className="flex gap-2">
                                    <Button
                                      type="button"
                                      variant="outline"
                                      onClick={() => setShowPaymentDialog(false)}
                                      className="flex-1"
                                    >
                                      Batal
                                    </Button>
                                    <Button onClick={handleAddPayment} className="flex-1">
                                      Simpan Pembayaran
                                    </Button>
                                  </div>
                                </div>
                              </DialogContent>
                            </Dialog>
                          </div>
                        )}
                      </div>
                    ))}
                  </div>
                )}
              </div>
            </div>
          ) : (
            <div className="flex flex-col items-center justify-center h-full text-muted-foreground">
              <Users className="w-16 h-16 mb-4 opacity-20" />
              <p className="text-lg font-medium">Pilih pelanggan untuk melihat detail</p>
              <p className="text-sm">Klik salah satu pelanggan di daftar sebelah kiri</p>
            </div>
          )}
        </Card>
      </div>
    </div>
  );
};