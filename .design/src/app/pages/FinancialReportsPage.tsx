import React, { useState, useMemo } from 'react';
import { useInventory } from '../contexts/InventoryContext';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { Badge } from '../components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '../components/ui/tabs';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '../components/ui/select';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '../components/ui/table';
import { toast } from 'sonner';
import { DollarSign, TrendingUp, TrendingDown, FileDown, BarChart3, PieChart } from 'lucide-react';
import { LineChart, Line, BarChart, Bar, XAxis, YAxis, CartesianGrid, Tooltip, Legend, ResponsiveContainer } from 'recharts';

export const FinancialReportsPage: React.FC = () => {
  const { products, sales, stockTransactions, operationalExpenses } = useInventory();
  const [reportPeriod, setReportPeriod] = useState<'daily' | 'monthly' | 'annual'>('monthly');
  const [trendPeriod, setTrendPeriod] = useState<'daily' | 'weekly' | 'monthly' | 'yearly'>('monthly');
  
  // Calculate financial metrics
  const totalRevenue = sales.reduce((sum, s) => sum + s.total_amount, 0);
  
  const totalCOGS = sales.flatMap(s => s.items).reduce((sum, item) => {
    const product = products.find(p => p.id === item.product_id);
    return sum + (product ? product.hpp * item.quantity : 0);
  }, 0);
  
  // Operational expenses
  const totalOperationalExpenses = operationalExpenses
    .filter(exp => exp.transaction_type === 'purchase')
    .reduce((sum, exp) => sum + exp.total_amount, 0);
  
  const grossProfit = totalRevenue - totalCOGS;
  const netProfit = grossProfit - totalOperationalExpenses;
  const grossProfitMargin = totalRevenue > 0 ? (grossProfit / totalRevenue) * 100 : 0;
  const netProfitMargin = totalRevenue > 0 ? (netProfit / totalRevenue) * 100 : 0;
  
  const totalInventoryValue = products.reduce((sum, p) => sum + (p.current_stock * p.hpp), 0);
  
  // Sales by product with profit analysis
  const productAnalysis = products.map(product => {
    const productSales = sales.flatMap(s => s.items).filter(item => item.product_id === product.id);
    const quantity = productSales.reduce((sum, item) => sum + item.quantity, 0);
    const revenue = productSales.reduce((sum, item) => sum + item.total, 0);
    const cogs = quantity * product.hpp;
    const profit = revenue - cogs;
    const profitMargin = revenue > 0 ? (profit / revenue) * 100 : 0;
    
    return {
      name: product.name,
      sku: product.sku,
      quantity,
      revenue,
      cogs,
      profit,
      profitMargin,
      hpp: product.hpp,
      sellingPrice: product.selling_price,
    };
  }).filter(p => p.quantity > 0)
    .sort((a, b) => b.profit - a.profit);

  // Memoize trend data to prevent re-generation on every render
  const trendData = useMemo(() => {
    switch (trendPeriod) {
      case 'daily': {
        // Last 14 days
        return Array.from({ length: 14 }, (_, i) => {
          const date = new Date();
          date.setDate(date.getDate() - (13 - i));
          const dayName = date.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
          
          // Simulate daily variations (using index for consistency)
          const baseRevenue = 8 + (i % 3) * 1.5;
          const revenue = baseRevenue;
          const cogs = revenue * (0.60 + (i % 5) * 0.02);
          const profit = revenue - cogs;
          
          return {
            period: dayName,
            revenue,
            cogs,
            profit,
          };
        });
      }
      
      case 'weekly': {
        // Last 12 weeks
        return Array.from({ length: 12 }, (_, i) => {
          const date = new Date();
          date.setDate(date.getDate() - ((11 - i) * 7));
          const weekNumber = Math.floor((date.getDate() - 1) / 7) + 1;
          const monthName = date.toLocaleDateString('id-ID', { month: 'short' });
          
          // Simulate weekly data (using index for consistency)
          const baseRevenue = 35 + i * 3;
          const revenue = baseRevenue + (i % 4) * 2;
          const cogs = revenue * 0.65;
          const profit = revenue - cogs;
          
          return {
            period: `W${weekNumber} ${monthName}`,
            revenue,
            cogs,
            profit,
          };
        });
      }
      
      case 'yearly': {
        // Last 5 years
        return Array.from({ length: 5 }, (_, i) => {
          const year = new Date().getFullYear() - (4 - i);
          
          // Simulate yearly growth (using index for consistency)
          const baseRevenue = 500 + i * 120;
          const revenue = baseRevenue + (i % 3) * 25;
          const cogs = revenue * 0.63;
          const profit = revenue - cogs;
          
          return {
            period: year.toString(),
            revenue,
            cogs,
            profit,
          };
        });
      }
      
      case 'monthly':
      default: {
        // Last 12 months
        return Array.from({ length: 12 }, (_, i) => {
          const date = new Date();
          date.setMonth(date.getMonth() - (11 - i));
          const monthName = date.toLocaleDateString('id-ID', { month: 'short', year: '2-digit' });
          
          // Simulate revenue growth (using index for consistency)
          const baseRevenue = 50 + i * 5;
          const revenue = baseRevenue + (i % 4) * 2.5;
          const cogs = revenue * 0.65;
          const profit = revenue - cogs;
          
          return {
            period: monthName,
            revenue,
            cogs,
            profit,
          };
        });
      }
    }
  }, [trendPeriod]);

  // Payment status breakdown
  const paymentStatusBreakdown = {
    paid: sales.filter(s => s.payment_status === 'paid').reduce((sum, s) => sum + s.total_amount, 0),
    pending: sales.filter(s => s.payment_status === 'pending').reduce((sum, s) => sum + s.total_amount, 0),
    partial: sales.filter(s => s.payment_status === 'partial').reduce((sum, s) => sum + s.total_amount, 0),
  };

  const exportToCSV = (data: any[], filename: string) => {
    const headers = Object.keys(data[0]);
    const csvContent = [
      headers.join(','),
      ...data.map(row => headers.map(header => row[header]).join(','))
    ].join('\n');
    
    const blob = new Blob([csvContent], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = `${filename}-${new Date().toISOString().split('T')[0]}.csv`;
    a.click();
    
    toast.success('Report exported to CSV');
  };

  const exportToPDF = () => {
    // In a real app, this would generate a proper PDF
    toast.info('PDF export would generate a detailed financial report');
  };

  return (
    <div className="space-y-6">
      <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h2 className="text-2xl font-bold text-slate-900">Financial Reports</h2>
          <p className="text-slate-600">Comprehensive financial analysis and insights</p>
        </div>
        <div className="flex gap-2">
          <Button variant="outline" onClick={() => exportToCSV(productAnalysis, 'product-analysis')}>
            <FileDown className="w-4 h-4 mr-2" />
            Export CSV
          </Button>
          <Button onClick={exportToPDF}>
            <FileDown className="w-4 h-4 mr-2" />
            Export PDF
          </Button>
        </div>
      </div>

      {/* Key Metrics */}
      <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Total Revenue</CardTitle>
            <DollarSign className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rp {(totalRevenue / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              From {sales.length} transactions
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">COGS / HPP</CardTitle>
            <TrendingDown className="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rp {(totalCOGS / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              Cost of goods sold
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Gross Profit</CardTitle>
            <TrendingUp className="h-4 w-4 text-blue-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rp {(grossProfit / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {grossProfitMargin.toFixed(1)}% margin
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Inventory Value</CardTitle>
            <BarChart3 className="h-4 w-4 text-purple-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold">
              Rp {(totalInventoryValue / 1000000).toFixed(1)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              Current stock value
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Operational Expenses</CardTitle>
            <TrendingDown className="h-4 w-4 text-red-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-red-600">
              Rp {(totalOperationalExpenses / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              Supplies & operational costs
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle className="text-sm font-medium">Net Profit</CardTitle>
            <TrendingUp className="h-4 w-4 text-green-600" />
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">
              Rp {(netProfit / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {netProfitMargin.toFixed(1)}% net margin
            </p>
          </CardContent>
        </Card>
      </div>

      {/* Profit & Loss Chart */}
      <Card>
        <CardHeader>
          <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <CardTitle>Profit & Loss Trend</CardTitle>
              <CardDescription>
                {trendPeriod === 'daily' && 'Daily revenue, COGS, and profit analysis (Last 14 days)'}
                {trendPeriod === 'weekly' && 'Weekly revenue, COGS, and profit analysis (Last 12 weeks)'}
                {trendPeriod === 'monthly' && 'Monthly revenue, COGS, and profit analysis (Last 12 months)'}
                {trendPeriod === 'yearly' && 'Yearly revenue, COGS, and profit analysis (Last 5 years)'}
              </CardDescription>
            </div>
            <div className="flex gap-2">
              <Button
                variant={trendPeriod === 'daily' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setTrendPeriod('daily')}
              >
                Hari
              </Button>
              <Button
                variant={trendPeriod === 'weekly' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setTrendPeriod('weekly')}
              >
                Minggu
              </Button>
              <Button
                variant={trendPeriod === 'monthly' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setTrendPeriod('monthly')}
              >
                Bulan
              </Button>
              <Button
                variant={trendPeriod === 'yearly' ? 'default' : 'outline'}
                size="sm"
                onClick={() => setTrendPeriod('yearly')}
              >
                Tahun
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <ResponsiveContainer width="100%" height={350}>
            <LineChart key={`chart-${trendPeriod}`} data={trendData}>
              <CartesianGrid strokeDasharray="3 3" />
              <XAxis dataKey="period" />
              <YAxis />
              <Tooltip formatter={(value: number) => `Rp ${value.toFixed(1)}M`} />
              <Legend />
              <Line type="monotone" dataKey="revenue" stroke="#10b981" strokeWidth={2} name="Revenue" />
              <Line type="monotone" dataKey="cogs" stroke="#ef4444" strokeWidth={2} name="COGS" />
              <Line type="monotone" dataKey="profit" stroke="#3b82f6" strokeWidth={2} name="Profit" />
            </LineChart>
          </ResponsiveContainer>
        </CardContent>
      </Card>

      {/* Product Profitability Analysis */}
      <Card>
        <CardHeader>
          <CardTitle>Product Profitability Analysis</CardTitle>
          <CardDescription>HPP (COGS) and profit margin by product</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Product</TableHead>
                  <TableHead>SKU</TableHead>
                  <TableHead>Qty Sold</TableHead>
                  <TableHead>HPP</TableHead>
                  <TableHead>Selling Price</TableHead>
                  <TableHead>Revenue</TableHead>
                  <TableHead>COGS</TableHead>
                  <TableHead>Gross Profit</TableHead>
                  <TableHead>Margin %</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {productAnalysis.map(product => (
                  <TableRow key={product.sku}>
                    <TableCell className="font-medium">{product.name}</TableCell>
                    <TableCell className="font-mono text-sm">{product.sku}</TableCell>
                    <TableCell>{product.quantity}</TableCell>
                    <TableCell>Rp {product.hpp.toLocaleString()}</TableCell>
                    <TableCell>Rp {product.sellingPrice.toLocaleString()}</TableCell>
                    <TableCell>Rp {product.revenue.toLocaleString()}</TableCell>
                    <TableCell className="text-red-600">
                      Rp {product.cogs.toLocaleString()}
                    </TableCell>
                    <TableCell className="font-bold text-green-600">
                      Rp {product.profit.toLocaleString()}
                    </TableCell>
                    <TableCell>
                      <Badge variant={product.profitMargin > 20 ? 'default' : 'secondary'}>
                        {product.profitMargin.toFixed(1)}%
                      </Badge>
                    </TableCell>
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </div>
        </CardContent>
      </Card>

      {/* Operational Expenses Breakdown */}
      <Card>
        <CardHeader>
          <CardTitle>Operational Expenses Breakdown</CardTitle>
          <CardDescription>Biaya operasional toko (kantong kresek, struk, pembersih, dll)</CardDescription>
        </CardHeader>
        <CardContent>
          <div className="overflow-x-auto">
            <Table>
              <TableHeader>
                <TableRow>
                  <TableHead>Date</TableHead>
                  <TableHead>Item Name</TableHead>
                  <TableHead>Type</TableHead>
                  <TableHead>Quantity</TableHead>
                  <TableHead>Unit Price</TableHead>
                  <TableHead>Total Amount</TableHead>
                  <TableHead>Notes</TableHead>
                </TableRow>
              </TableHeader>
              <TableBody>
                {operationalExpenses.length === 0 ? (
                  <TableRow>
                    <TableCell colSpan={7} className="text-center py-8 text-slate-500">
                      No operational expenses recorded
                    </TableCell>
                  </TableRow>
                ) : (
                  operationalExpenses
                    .filter(exp => exp.transaction_type === 'purchase')
                    .sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
                    .map(expense => (
                      <TableRow key={expense.id}>
                        <TableCell>
                          {new Date(expense.date).toLocaleDateString('id-ID', {
                            day: 'numeric',
                            month: 'short',
                            year: 'numeric',
                          })}
                        </TableCell>
                        <TableCell className="font-medium">{expense.item_name}</TableCell>
                        <TableCell>
                          <Badge variant={expense.transaction_type === 'purchase' ? 'default' : 'secondary'}>
                            {expense.transaction_type === 'purchase' ? 'Purchase' : 'Usage'}
                          </Badge>
                        </TableCell>
                        <TableCell>{expense.quantity}</TableCell>
                        <TableCell>Rp {expense.unit_price.toLocaleString()}</TableCell>
                        <TableCell className="font-semibold text-red-600">
                          Rp {expense.total_amount.toLocaleString()}
                        </TableCell>
                        <TableCell className="text-sm text-slate-500">
                          {expense.notes || '-'}
                        </TableCell>
                      </TableRow>
                    ))
                )}
              </TableBody>
            </Table>
          </div>
          <div className="mt-4 pt-4 border-t">
            <div className="flex items-center justify-between">
              <span className="text-sm font-medium text-slate-600">Total Operational Expenses:</span>
              <span className="text-2xl font-bold text-red-600">
                Rp {totalOperationalExpenses.toLocaleString()}
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      {/* Payment Status */}
      <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Paid Invoices</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-green-600">
              Rp {(paymentStatusBreakdown.paid / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {sales.filter(s => s.payment_status === 'paid').length} invoices
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Pending Payments</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-yellow-600">
              Rp {(paymentStatusBreakdown.pending / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {sales.filter(s => s.payment_status === 'pending').length} invoices
            </p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle className="text-sm">Partial Payments</CardTitle>
          </CardHeader>
          <CardContent>
            <div className="text-2xl font-bold text-orange-600">
              Rp {(paymentStatusBreakdown.partial / 1000000).toFixed(2)}M
            </div>
            <p className="text-xs text-slate-500 mt-1">
              {sales.filter(s => s.payment_status === 'partial').length} invoices
            </p>
          </CardContent>
        </Card>
      </div>
    </div>
  );
};