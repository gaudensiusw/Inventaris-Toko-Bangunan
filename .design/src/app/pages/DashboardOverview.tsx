import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { useAuth } from '../contexts/AuthContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Badge } from '../components/ui/badge';
import { Button } from '../components/ui/button';
import { Tabs, TabsList, TabsTrigger } from '../components/ui/tabs';
import { Popover, PopoverContent, PopoverTrigger } from '../components/ui/popover';
import { Calendar } from '../components/ui/calendar';
import {
  Package,
  TrendingUp,
  TrendingDown,
  DollarSign,
  AlertTriangle,
  ShoppingCart,
  CreditCard,
  Wallet,
  CalendarIcon,
  Clock,
} from 'lucide-react';
import { LineChart, Line, BarChart, Bar, PieChart, Pie, Cell, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';
import { format, subDays, subMonths, startOfWeek, endOfWeek, startOfMonth, endOfMonth, isWithinInterval, differenceInDays } from 'date-fns';

type TimePeriod = 'day' | 'week' | 'month' | 'custom';

export const DashboardOverview: React.FC = () => {
  const { products, stockNotifications, sales, stockTransactions } = useInventory();
  const { user } = useAuth();

  const [timePeriod, setTimePeriod] = useState<TimePeriod>('week');
  const [customDateRange, setCustomDateRange] = useState<{ from?: Date; to?: Date }>({});

  // Get date range based on selected period
  const getDateRange = () => {
    const today = new Date();
    
    switch (timePeriod) {
      case 'day':
        return { from: today, to: today };
      case 'week':
        return { from: startOfWeek(today), to: endOfWeek(today) };
      case 'month':
        return { from: startOfMonth(today), to: endOfMonth(today) };
      case 'custom':
        return customDateRange.from && customDateRange.to 
          ? customDateRange 
          : { from: subDays(today, 7), to: today };
      default:
        return { from: subDays(today, 7), to: today };
    }
  };

  const dateRange = getDateRange();

  // Filter sales based on date range
  const filteredSales = useMemo(() => {
    if (!dateRange.from || !dateRange.to) return sales;
    
    return sales.filter(sale => {
      const saleDate = new Date(sale.created_at);
      return isWithinInterval(saleDate, { start: dateRange.from!, end: dateRange.to! });
    });
  }, [sales, dateRange]);

  // Calculate statistics
  const totalProducts = products.length;
  const lowStockProducts = products.filter(p => p.current_stock < p.min_stock).length;
  const totalStockValue = products.reduce((sum, p) => sum + (p.current_stock * p.hpp), 0);
  
  // Cash vs Credit breakdown
  const cashSales = filteredSales.filter(s => s.payment_method === 'cash' || s.payment_method === 'transfer');
  const creditSales = filteredSales.filter(s => s.payment_method === 'credit');
  
  const totalCashRevenue = cashSales.reduce((sum, s) => sum + s.total_amount, 0);
  const totalCreditRevenue = creditSales.reduce((sum, s) => sum + s.total_amount, 0);
  const totalRevenue = totalCashRevenue + totalCreditRevenue;
  
  // Profit calculation
  const totalCost = filteredSales.reduce((sum, s) => sum + (s.cost_amount || 0), 0);
  const totalProfit = totalRevenue - totalCost;
  const profitMargin = totalRevenue > 0 ? (totalProfit / totalRevenue) * 100 : 0;

  // Receivables (Piutang)
  const pendingReceivables = sales.filter(s => s.payment_status === 'pending' && s.payment_method === 'credit');
  const partialReceivables = sales.filter(s => s.payment_status === 'partial' && s.payment_method === 'credit');
  
  const totalPiutang = [...pendingReceivables, ...partialReceivables].reduce((sum, s) => {
    const remaining = s.payment_status === 'partial' 
      ? s.total_amount - (s.paid_amount || 0)
      : s.total_amount;
    return sum + remaining;
  }, 0);

  // Overdue receivables
  const today = new Date();
  const overdueReceivables = [...pendingReceivables, ...partialReceivables].filter(s => {
    if (!s.due_date) return false;
    return new Date(s.due_date) < today;
  });

  const dueSoonReceivables = [...pendingReceivables, ...partialReceivables].filter(s => {
    if (!s.due_date) return false;
    const dueDate = new Date(s.due_date);
    const daysUntilDue = differenceInDays(dueDate, today);
    return daysUntilDue >= 0 && daysUntilDue <= 3; // Due within 3 days
  });

  // Today's sales
  const todaySales = sales.filter(s => {
    const saleDate = new Date(s.created_at);
    return saleDate.toDateString() === today.toDateString();
  });
  const todaySalesAmount = todaySales.reduce((sum, s) => sum + s.total_amount, 0);

  // Sales trend data based on period
  const salesTrendData = useMemo(() => {
    const dataPoints: { id: string; label: string; revenue: number; profit: number; cash: number; credit: number }[] = [];

    if (timePeriod === 'day') {
      // For day view, show last 24 hours
      const now = new Date();
      for (let i = 23; i >= 0; i--) {
        const hourDate = new Date(now);
        hourDate.setHours(now.getHours() - i, 0, 0, 0);
        const hour = hourDate.getHours();
        const label = `${hour.toString().padStart(2, '0')}:00`;
        const uniqueId = `${format(now, 'yyyy-MM-dd')}-hour-${hour}`;

        const hourSales = filteredSales.filter(s => {
          const saleDate = new Date(s.created_at);
          return saleDate.getHours() === hour && 
                 saleDate.toDateString() === now.toDateString();
        });

        const revenue = hourSales.reduce((sum, s) => sum + s.total_amount, 0);
        const cost = hourSales.reduce((sum, s) => sum + (s.cost_amount || 0), 0);
        const cash = hourSales.filter(s => s.payment_method === 'cash' || s.payment_method === 'transfer')
          .reduce((sum, s) => sum + s.total_amount, 0);
        const credit = hourSales.filter(s => s.payment_method === 'credit')
          .reduce((sum, s) => sum + s.total_amount, 0);

        dataPoints.push({
          id: uniqueId,
          label,
          revenue: revenue / 1000000,
          profit: (revenue - cost) / 1000000,
          cash: cash / 1000000,
          credit: credit / 1000000,
        });
      }
    } else {
      // For week and month views
      const dayCount = timePeriod === 'week' ? 7 : 30;
      for (let i = dayCount - 1; i >= 0; i--) {
        const date = subDays(dateRange.to || new Date(), i);
        const uniqueId = format(date, 'yyyy-MM-dd');
        const label = format(date, 'MMM dd');

        const daySales = filteredSales.filter(s => {
          const saleDate = new Date(s.created_at);
          return saleDate.toDateString() === date.toDateString();
        });

        const revenue = daySales.reduce((sum, s) => sum + s.total_amount, 0);
        const cost = daySales.reduce((sum, s) => sum + (s.cost_amount || 0), 0);
        const cash = daySales.filter(s => s.payment_method === 'cash' || s.payment_method === 'transfer')
          .reduce((sum, s) => sum + s.total_amount, 0);
        const credit = daySales.filter(s => s.payment_method === 'credit')
          .reduce((sum, s) => sum + s.total_amount, 0);

        dataPoints.push({
          id: uniqueId,
          label,
          revenue: revenue / 1000000,
          profit: (revenue - cost) / 1000000,
          cash: cash / 1000000,
          credit: credit / 1000000,
        });
      }
    }

    // Remove any potential duplicates and filter out invalid entries
    const uniqueDataPoints = Array.from(
      new Map(dataPoints.map(item => [item.id, item])).values()
    ).filter(item => item.id && item.label); // Ensure no null/undefined ids or labels

    return uniqueDataPoints;
  }, [filteredSales, timePeriod, dateRange]);

  // Payment Methods
  const paymentData = useMemo(() => {
    return [
      { id: 'payment-cash', name: 'Cash', value: cashSales.filter(s => s.payment_method === 'cash').reduce((sum, s) => sum + s.total_amount, 0) },
      { id: 'payment-transfer', name: 'Transfer', value: cashSales.filter(s => s.payment_method === 'transfer').reduce((sum, s) => sum + s.total_amount, 0) },
      { id: 'payment-credit', name: 'Kredit', value: totalCreditRevenue },
    ].filter(p => p.value > 0 && p.name); // Filter valid entries only
  }, [cashSales, totalCreditRevenue]);

  // Top selling products
  const topProducts = useMemo(() => {
    const productSales = filteredSales.flatMap(s => s.items).reduce((acc, item) => {
      const productName = products.find(p => p.id === item.product_id)?.name || 'Unknown';
      acc[productName] = (acc[productName] || 0) + item.quantity;
      return acc;
    }, {} as Record<string, number>);

    return Object.entries(productSales)
      .sort((a, b) => b[1] - a[1])
      .slice(0, 5)
      .map(([name, quantity], index) => ({ 
        id: `product-${index}-${name}`, 
        name, 
        quantity 
      }))
      .filter(item => item.name && item.id); // Ensure valid entries
  }, [filteredSales, products]);

  const COLORS = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444'];

  return (
    <div className="space-y-6">
      {/* Welcome Section */}
      <div className="flex items-center justify-between">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">
            Selamat Datang, {user?.name}!
          </h2>
          <p className="text-slate-600 mt-1">
            Ringkasan inventori dan penjualan Anda
          </p>
        </div>

        {/* Time Period Selector */}
        <div className="flex items-center gap-3">
          <Tabs value={timePeriod} onValueChange={(v) => setTimePeriod(v as TimePeriod)}>
            <TabsList>
              <TabsTrigger value="day">Hari</TabsTrigger>
              <TabsTrigger value="week">Minggu</TabsTrigger>
              <TabsTrigger value="month">Bulan</TabsTrigger>
              <TabsTrigger value="custom">Custom</TabsTrigger>
            </TabsList>
          </Tabs>

          {timePeriod === 'custom' && (
            <Popover>
              <PopoverTrigger asChild>
                <Button variant="outline" className="w-[240px] justify-start text-left">
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {customDateRange.from && customDateRange.to ? (
                    <>
                      {format(customDateRange.from, 'MMM dd')} - {format(customDateRange.to, 'MMM dd')}
                    </>
                  ) : (
                    'Pilih tanggal'
                  )}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0" align="end">
                <Calendar
                  mode="range"
                  selected={{ from: customDateRange.from, to: customDateRange.to }}
                  onSelect={(range: any) => setCustomDateRange(range || {})}
                  numberOfMonths={2}
                />
              </PopoverContent>
            </Popover>
          )}
        </div>
      </div>

      {/* Alerts for Overdue & Due Soon */}
      {(overdueReceivables.length > 0 || dueSoonReceivables.length > 0) && (
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {overdueReceivables.length > 0 && (
            <Card className="border-red-300 bg-red-50">
              <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-red-900 text-base">
                  <AlertTriangle className="w-5 h-5" />
                  Piutang Jatuh Tempo ({overdueReceivables.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  {overdueReceivables.slice(0, 3).map(sale => (
                    <div key={sale.id} className="flex items-center justify-between p-2 bg-white rounded text-sm">
                      <div>
                        <p className="font-medium">{sale.customer_name}</p>
                        <p className="text-xs text-slate-600">{sale.sale_number}</p>
                      </div>
                      <div className="text-right">
                        <p className="font-bold text-red-600">
                          Rp {((sale.total_amount - (sale.paid_amount || 0)) / 1000000).toFixed(1)}M
                        </p>
                        <p className="text-xs text-red-500">
                          {differenceInDays(today, new Date(sale.due_date!))} hari terlambat
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}

          {dueSoonReceivables.length > 0 && (
            <Card className="border-orange-300 bg-orange-50">
              <CardHeader className="pb-3">
                <CardTitle className="flex items-center gap-2 text-orange-900 text-base">
                  <Clock className="w-5 h-5" />
                  Jatuh Tempo Segera ({dueSoonReceivables.length})
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div className="space-y-2">
                  {dueSoonReceivables.slice(0, 3).map(sale => (
                    <div key={sale.id} className="flex items-center justify-between p-2 bg-white rounded text-sm">
                      <div>
                        <p className="font-medium">{sale.customer_name}</p>
                        <p className="text-xs text-slate-600">{sale.sale_number}</p>
                      </div>
                      <div className="text-right">
                        <p className="font-bold text-orange-600">
                          Rp {((sale.total_amount - (sale.paid_amount || 0)) / 1000000).toFixed(1)}M
                        </p>
                        <p className="text-xs text-orange-500">
                          {differenceInDays(new Date(sale.due_date!), today)} hari lagi
                        </p>
                      </div>
                    </div>
                  ))}
                </div>
              </CardContent>
            </Card>
          )}
        </div>
      )}

      {/* Stats Cards */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Pendapatan (Cash)</CardTitle>
            <Wallet className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">
              Rp {(totalCashRevenue / 1000000).toFixed(1)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {cashSales.length} transaksi tunai/transfer
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Piutang (Kredit)</CardTitle>
            <CreditCard className="h-4 w-4 text-orange-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-orange-600">
              Rp {(totalPiutang / 1000000).toFixed(1)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {pendingReceivables.length + partialReceivables.length} invoice belum lunas
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Laba Bersih</CardTitle>
            <TrendingUp className="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-blue-600">
              Rp {(totalProfit / 1000000).toFixed(1)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              Margin: {profitMargin.toFixed(1)}%
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Nilai Stok</CardTitle>
            <Package className="h-4 w-4 text-purple-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rp {(totalStockValue / 1000000).toFixed(1)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {totalProducts} produk ({lowStockProducts} stok rendah)
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Sales Trend */}
        <Card>
          <CardHeader>
            <CardTitle>Tren Penjualan & Laba</CardTitle>
            <CardDescription>
              {timePeriod === 'day' ? '24 Jam Terakhir' : 
               timePeriod === 'week' ? '7 Hari Terakhir' : 
               timePeriod === 'month' ? '30 Hari Terakhir' : 'Periode Custom'}
            </CardDescription>
          </CardHeader>
          <CardContent>
            {salesTrendData.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <LineChart key={`sales-trend-${timePeriod}`} data={salesTrendData}>
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="label" />
                  <YAxis />
                  <Tooltip 
                    formatter={(value: number) => `Rp ${value.toFixed(2)}M`}
                    labelStyle={{ color: '#000' }}
                  />
                  <Legend />
                  <Line 
                    type="monotone" 
                    dataKey="revenue" 
                    stroke="#3b82f6" 
                    strokeWidth={2}
                    name="Pendapatan (M)"
                  />
                  <Line 
                    type="monotone" 
                    dataKey="profit" 
                    stroke="#10b981" 
                    strokeWidth={2}
                    name="Laba (M)"
                  />
                </LineChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-[300px] flex items-center justify-center text-slate-400">
                Tidak ada data penjualan
              </div>
            )}
          </CardContent>
        </Card>

        {/* Cash vs Credit */}
        <Card>
          <CardHeader>
            <CardTitle>Pendapatan: Cash vs Kredit</CardTitle>
            <CardDescription>Perbandingan metode pembayaran</CardDescription>
          </CardHeader>
          <CardContent>
            {salesTrendData.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={salesTrendData} key="cash-credit-chart">
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="label" />
                  <YAxis />
                  <Tooltip formatter={(value: number) => `Rp ${value.toFixed(2)}M`} />
                  <Legend />
                  <Bar key="bar-cash" dataKey="cash" fill="#10b981" name="Cash/Transfer (M)" stackId="a" />
                  <Bar key="bar-credit" dataKey="credit" fill="#f59e0b" name="Kredit (M)" stackId="a" />
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-[300px] flex items-center justify-center text-slate-400">
                Tidak ada data penjualan
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Second Charts Row */}
      <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {/* Payment Methods Pie */}
        <Card>
          <CardHeader>
            <CardTitle>Metode Pembayaran</CardTitle>
            <CardDescription>Distribusi penjualan per metode</CardDescription>
          </CardHeader>
          <CardContent>
            {paymentData.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <PieChart key="payment-methods-chart">
                  <Pie
                    data={paymentData}
                    cx="50%"
                    cy="50%"
                    labelLine={false}
                    label={({ name, percent }) => `${name}: ${(percent * 100).toFixed(0)}%`}
                    outerRadius={100}
                    fill="#8884d8"
                    dataKey="value"
                  >
                    {paymentData.map((entry, index) => (
                      <Cell key={entry.id || `cell-${index}`} fill={COLORS[index % COLORS.length]} />
                    ))}
                  </Pie>
                  <Tooltip 
                    formatter={(value: number) => `Rp ${(value / 1000000).toFixed(2)}M`}
                  />
                </PieChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-[300px] flex items-center justify-center text-slate-400">
                Tidak ada data pembayaran
              </div>
            )}
          </CardContent>
        </Card>

        {/* Top Products */}
        <Card>
          <CardHeader>
            <CardTitle>Produk Terlaris</CardTitle>
            <CardDescription>Produk dengan penjualan tertinggi</CardDescription>
          </CardHeader>
          <CardContent>
            {topProducts.length > 0 ? (
              <ResponsiveContainer width="100%" height={300}>
                <BarChart data={topProducts} key="top-products-chart">
                  <CartesianGrid strokeDasharray="3 3" />
                  <XAxis dataKey="name" />
                  <YAxis />
                  <Tooltip />
                  <Legend />
                  <Bar key="bar-quantity" dataKey="quantity" fill="#10b981" name="Jumlah Terjual" />
                </BarChart>
              </ResponsiveContainer>
            ) : (
              <div className="h-[300px] flex items-center justify-center text-slate-400">
                Tidak ada data produk
              </div>
            )}
          </CardContent>
        </Card>
      </div>

      {/* Low Stock Alerts */}
      {lowStockProducts > 0 && (
        <Card className="border-red-200 bg-red-50">
          <CardHeader>
            <CardTitle className="flex items-center gap-2 text-red-900">
              <AlertTriangle className="w-5 h-5" />
              Peringatan Stok Rendah
            </CardTitle>
            <CardDescription className="text-red-700">
              {lowStockProducts} produk di bawah stok minimum
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-2">
              {products
                .filter(p => p.current_stock < p.min_stock)
                .slice(0, 5)
                .map(product => (
                  <div
                    key={product.id}
                    className="flex items-center justify-between p-3 bg-white rounded-lg"
                  >
                    <div>
                      <p className="font-medium text-slate-900">{product.name}</p>
                      <p className="text-sm text-slate-600">SKU: {product.sku}</p>
                    </div>
                    <div className="text-right">
                      <Badge variant="destructive">
                        {product.current_stock} / {product.min_stock}
                      </Badge>
                      <p className="text-xs text-slate-600 mt-1">
                        {product.base_unit}
                      </p>
                    </div>
                  </div>
                ))}
            </div>
          </CardContent>
        </Card>
      )}
    </div>
  );
};