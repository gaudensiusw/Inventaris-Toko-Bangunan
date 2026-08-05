import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import {
  Calendar,
  DollarSign,
  AlertTriangle,
  CheckCircle,
  Clock,
  TrendingUp,
  Filter,
  ChevronLeft,
  ChevronRight,
} from 'lucide-react';
import { cn } from '../components/ui/utils';

export const PayablesPage: React.FC = () => {
  const { user } = useAuth();
  const { stockTransactions, products, suppliers } = useInventory();
  const [selectedMonth, setSelectedMonth] = useState(new Date());
  const [filterStatus, setFilterStatus] = useState<'all' | 'unpaid' | 'partial' | 'paid'>('all');

  // Get all purchase transactions with payment info
  const purchaseTransactions = useMemo(() => {
    return stockTransactions
      .filter(txn => txn.transaction_type === 'in' && txn.supplier_id && txn.due_date)
      .map(txn => {
        const product = products.find(p => p.id === txn.product_id);
        const supplier = suppliers.find(s => s.id === txn.supplier_id);
        const dueDate = new Date(txn.due_date!);
        const today = new Date();
        const isOverdue = dueDate < today && txn.payment_status !== 'paid';
        const daysUntilDue = Math.ceil((dueDate.getTime() - today.getTime()) / (1000 * 60 * 60 * 24));

        return {
          ...txn,
          product,
          supplier,
          dueDate,
          isOverdue,
          daysUntilDue,
          remainingAmount: txn.total_amount - (txn.amount_paid || 0),
        };
      })
      .filter(txn => {
        if (filterStatus === 'all') return true;
        return txn.payment_status === filterStatus;
      })
      .sort((a, b) => a.dueDate.getTime() - b.dueDate.getTime());
  }, [stockTransactions, products, suppliers, filterStatus]);

  // Calculate summary stats
  const summaryStats = useMemo(() => {
    const total = purchaseTransactions.reduce((sum, txn) => sum + txn.remainingAmount, 0);
    const overdue = purchaseTransactions
      .filter(txn => txn.isOverdue)
      .reduce((sum, txn) => sum + txn.remainingAmount, 0);
    const dueThisMonth = purchaseTransactions
      .filter(txn => {
        const dueMonth = txn.dueDate.getMonth();
        const dueYear = txn.dueDate.getFullYear();
        return dueMonth === selectedMonth.getMonth() && dueYear === selectedMonth.getFullYear();
      })
      .reduce((sum, txn) => sum + txn.remainingAmount, 0);
    const upcoming = purchaseTransactions
      .filter(txn => !txn.isOverdue && txn.payment_status !== 'paid')
      .reduce((sum, txn) => sum + txn.remainingAmount, 0);

    return { total, overdue, dueThisMonth, upcoming };
  }, [purchaseTransactions, selectedMonth]);

  // Group transactions by due date for calendar view
  const transactionsByDate = useMemo(() => {
    const grouped: { [date: string]: typeof purchaseTransactions } = {};
    purchaseTransactions.forEach(txn => {
      const dateKey = txn.dueDate.toISOString().split('T')[0];
      if (!grouped[dateKey]) {
        grouped[dateKey] = [];
      }
      grouped[dateKey].push(txn);
    });
    return grouped;
  }, [purchaseTransactions]);

  // Generate calendar days for selected month
  const calendarDays = useMemo(() => {
    const year = selectedMonth.getFullYear();
    const month = selectedMonth.getMonth();
    const firstDay = new Date(year, month, 1);
    const lastDay = new Date(year, month + 1, 0);
    const startDate = new Date(firstDay);
    startDate.setDate(startDate.getDate() - firstDay.getDay()); // Start from Sunday

    const days = [];
    const endDate = new Date(lastDay);
    endDate.setDate(endDate.getDate() + (6 - lastDay.getDay())); // End on Saturday

    for (let d = new Date(startDate); d <= endDate; d.setDate(d.getDate() + 1)) {
      days.push(new Date(d));
    }

    return days;
  }, [selectedMonth]);

  const handlePreviousMonth = () => {
    setSelectedMonth(new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() - 1, 1));
  };

  const handleNextMonth = () => {
    setSelectedMonth(new Date(selectedMonth.getFullYear(), selectedMonth.getMonth() + 1, 1));
  };

  const getStatusBadge = (status: string) => {
    switch (status) {
      case 'paid':
        return <Badge className="bg-green-100 text-green-700">Lunas</Badge>;
      case 'partial':
        return <Badge className="bg-yellow-100 text-yellow-700">Sebagian</Badge>;
      case 'unpaid':
        return <Badge className="bg-red-100 text-red-700">Belum Bayar</Badge>;
      default:
        return null;
    }
  };

  const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('id-ID', {
      style: 'currency',
      currency: 'IDR',
      minimumFractionDigits: 0,
    }).format(amount);
  };

  const formatDate = (date: Date | string) => {
    if (!date) return '';
    const dateObj = date instanceof Date ? date : new Date(date);
    if (isNaN(dateObj.getTime())) return '';
    return new Intl.DateTimeFormat('id-ID', {
      day: 'numeric',
      month: 'short',
      year: 'numeric',
    }).format(dateObj);
  };

  return (
    <div className="space-y-6">
      {/* Header */}
      <div className="flex items-center justify-between">
        <div>
          <h1 className="text-2xl font-bold text-slate-900">Tagihan Supplier</h1>
          <p className="text-slate-600 mt-1">
            Kelola dan pantau jadwal pembayaran ke supplier
          </p>
        </div>
      </div>

      {/* Summary Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        <Card className="p-6">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-blue-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <DollarSign className="w-7 h-7 text-blue-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600 mb-1">Total Hutang</p>
              <p className="text-2xl font-bold text-slate-900">{formatCurrency(summaryStats.total)}</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-red-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <AlertTriangle className="w-7 h-7 text-red-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600 mb-1">Jatuh Tempo</p>
              <p className="text-2xl font-bold text-red-900">{formatCurrency(summaryStats.overdue)}</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <Clock className="w-7 h-7 text-orange-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600 mb-1">Bulan Ini</p>
              <p className="text-2xl font-bold text-orange-900">{formatCurrency(summaryStats.dueThisMonth)}</p>
            </div>
          </div>
        </Card>

        <Card className="p-6">
          <div className="flex items-center gap-4">
            <div className="w-14 h-14 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
              <TrendingUp className="w-7 h-7 text-green-600" />
            </div>
            <div className="flex-1">
              <p className="text-sm text-slate-600 mb-1">Akan Datang</p>
              <p className="text-2xl font-bold text-green-900">{formatCurrency(summaryStats.upcoming)}</p>
            </div>
          </div>
        </Card>
      </div>

      {/* Calendar View */}
      <Card className="p-6">
        <div className="flex items-center justify-between mb-6">
          <h2 className="text-xl font-bold text-slate-900">Kalender Jatuh Tempo</h2>
          <div className="flex items-center gap-2">
            <Button variant="outline" size="sm" onClick={handlePreviousMonth}>
              <ChevronLeft className="w-4 h-4" />
            </Button>
            <div className="text-center min-w-[150px]">
              <p className="font-semibold">
                {selectedMonth.toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}
              </p>
            </div>
            <Button variant="outline" size="sm" onClick={handleNextMonth}>
              <ChevronRight className="w-4 h-4" />
            </Button>
          </div>
        </div>

        {/* Calendar Grid */}
        <div className="grid grid-cols-7 gap-2">
          {/* Day headers */}
          {['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab'].map(day => (
            <div key={day} className="text-center text-sm font-semibold text-slate-600 py-2">
              {day}
            </div>
          ))}

          {/* Calendar days */}
          {calendarDays.map((day, index) => {
            const dateKey = day.toISOString().split('T')[0];
            const dayTransactions = transactionsByDate[dateKey] || [];
            const isCurrentMonth = day.getMonth() === selectedMonth.getMonth();
            const isToday = day.toDateString() === new Date().toDateString();
            const hasOverdue = dayTransactions.some(txn => txn.isOverdue);
            const hasUnpaid = dayTransactions.some(txn => txn.payment_status === 'unpaid');
            const totalAmount = dayTransactions.reduce((sum, txn) => sum + txn.remainingAmount, 0);

            return (
              <div
                key={index}
                className={cn(
                  'min-h-[80px] p-2 border rounded-lg',
                  !isCurrentMonth && 'bg-slate-50 text-slate-400',
                  isCurrentMonth && 'bg-white',
                  isToday && 'border-blue-500 border-2',
                  hasOverdue && isCurrentMonth && 'bg-red-50',
                  dayTransactions.length > 0 && isCurrentMonth && !hasOverdue && 'bg-orange-50'
                )}
              >
                <div className="text-sm font-semibold mb-1">{day.getDate()}</div>
                {dayTransactions.length > 0 && (
                  <div className="space-y-1">
                    <div className={cn(
                      'text-xs font-semibold px-1 py-0.5 rounded',
                      hasOverdue ? 'bg-red-600 text-white' : 'bg-orange-600 text-white'
                    )}>
                      {dayTransactions.length} tagihan
                    </div>
                    <div className="text-xs text-slate-700 font-medium">
                      {formatCurrency(totalAmount)}
                    </div>
                  </div>
                )}
              </div>
            );
          })}
        </div>

        {/* Legend */}
        <div className="flex items-center gap-4 mt-6 pt-4 border-t">
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 bg-red-50 border border-red-200 rounded"></div>
            <span className="text-sm text-slate-600">Terlambat</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 bg-orange-50 border border-orange-200 rounded"></div>
            <span className="text-sm text-slate-600">Akan Jatuh Tempo</span>
          </div>
          <div className="flex items-center gap-2">
            <div className="w-4 h-4 border-2 border-blue-500 rounded"></div>
            <span className="text-sm text-slate-600">Hari Ini</span>
          </div>
        </div>
      </Card>

      {/* Transaction List */}
      <Card className="p-6">
        <div className="flex items-center justify-between mb-4">
          <h2 className="text-xl font-bold text-slate-900">Daftar Tagihan</h2>
          <div className="flex items-center gap-2">
            <Filter className="w-4 h-4 text-slate-600" />
            <select
              value={filterStatus}
              onChange={(e) => setFilterStatus(e.target.value as any)}
              className="px-3 py-2 border rounded-lg text-sm"
            >
              <option value="all">Semua Status</option>
              <option value="unpaid">Belum Bayar</option>
              <option value="partial">Sebagian</option>
              <option value="paid">Lunas</option>
            </select>
          </div>
        </div>

        <div className="overflow-x-auto">
          <table className="w-full">
            <thead>
              <tr className="border-b">
                <th className="text-left py-3 px-4 font-semibold text-slate-700">No. Referensi</th>
                <th className="text-left py-3 px-4 font-semibold text-slate-700">Tanggal Masuk</th>
                <th className="text-left py-3 px-4 font-semibold text-slate-700">Jatuh Tempo</th>
                <th className="text-left py-3 px-4 font-semibold text-slate-700">Supplier</th>
                <th className="text-left py-3 px-4 font-semibold text-slate-700">Produk</th>
                <th className="text-right py-3 px-4 font-semibold text-slate-700">Total</th>
                <th className="text-right py-3 px-4 font-semibold text-slate-700">Terbayar</th>
                <th className="text-right py-3 px-4 font-semibold text-slate-700">Sisa</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Status</th>
                <th className="text-center py-3 px-4 font-semibold text-slate-700">Tempo</th>
              </tr>
            </thead>
            <tbody>
              {purchaseTransactions.map((txn) => (
                <tr key={txn.id} className="border-b hover:bg-slate-50">
                  <td className="py-3 px-4">
                    <p className="font-medium text-slate-900">{txn.reference_number}</p>
                  </td>
                  <td className="py-3 px-4">
                    <p className="text-sm text-slate-700">
                      {formatDate(new Date(txn.created_at))}
                    </p>
                  </td>
                  <td className="py-3 px-4">
                    <p className={cn(
                      'text-sm font-medium',
                      txn.isOverdue ? 'text-red-700' : 'text-slate-700'
                    )}>
                      {formatDate(txn.dueDate)}
                    </p>
                  </td>
                  <td className="py-3 px-4">
                    <p className="text-sm text-slate-900">{txn.supplier?.name}</p>
                  </td>
                  <td className="py-3 px-4">
                    <p className="text-sm text-slate-900">{txn.product?.name}</p>
                    <p className="text-xs text-slate-500">
                      {txn.quantity_in_unit} {txn.unit_used}
                    </p>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <p className="font-medium text-slate-900">{formatCurrency(txn.total_amount)}</p>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <p className="text-sm text-green-700">{formatCurrency(txn.amount_paid || 0)}</p>
                  </td>
                  <td className="py-3 px-4 text-right">
                    <p className="font-medium text-slate-900">{formatCurrency(txn.remainingAmount)}</p>
                  </td>
                  <td className="py-3 px-4 text-center">
                    {getStatusBadge(txn.payment_status!)}
                  </td>
                  <td className="py-3 px-4 text-center">
                    {txn.isOverdue ? (
                      <Badge className="bg-red-100 text-red-700">
                        Terlambat {Math.abs(txn.daysUntilDue)} hari
                      </Badge>
                    ) : txn.payment_status === 'paid' ? (
                      <Badge className="bg-green-100 text-green-700">
                        <CheckCircle className="w-3 h-3 mr-1" />
                        Lunas
                      </Badge>
                    ) : (
                      <Badge className="bg-orange-100 text-orange-700">
                        {txn.daysUntilDue} hari lagi
                      </Badge>
                    )}
                  </td>
                </tr>
              ))}
            </tbody>
          </table>
        </div>

        {purchaseTransactions.length === 0 && (
          <div className="text-center py-12">
            <Calendar className="w-12 h-12 text-slate-300 mx-auto mb-3" />
            <p className="text-slate-600">Tidak ada tagihan ditemukan</p>
          </div>
        )}
      </Card>
    </div>
  );
};