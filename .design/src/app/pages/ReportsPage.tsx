import React from 'react';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '../components/ui/card';
import { Button } from '../components/ui/button';
import { FileText, Download } from 'lucide-react';
import { toast } from 'sonner';

export const ReportsPage: React.FC = () => {
  const handleExport = (reportType: string) => {
    toast.success(`Exporting ${reportType} report...`);
  };

  const reports = [
    {
      title: 'Stock Summary Report',
      description: 'Current stock levels for all products',
      type: 'stock_summary',
    },
    {
      title: 'Sales Report',
      description: 'Sales transactions and revenue',
      type: 'sales',
    },
    {
      title: 'Purchase Order Report',
      description: 'Supplier purchase orders',
      type: 'purchase_orders',
    },
    {
      title: 'Inventory Valuation',
      description: 'Current inventory value at cost',
      type: 'inventory_valuation',
    },
  ];

  return (
    <div className="space-y-6">
      <div>
        <h2 className="text-2xl font-bold text-slate-900">Basic Reports</h2>
        <p className="text-slate-600">Generate and export operational reports</p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
        {reports.map(report => (
          <Card key={report.type}>
            <CardHeader>
              <CardTitle className="flex items-center gap-2">
                <FileText className="w-5 h-5 text-blue-600" />
                {report.title}
              </CardTitle>
              <CardDescription>{report.description}</CardDescription>
            </CardHeader>
            <CardContent className="space-y-2">
              <Button
                variant="outline"
                className="w-full"
                onClick={() => handleExport(report.title)}
              >
                <Download className="w-4 h-4 mr-2" />
                Export as CSV
              </Button>
              <Button
                variant="outline"
                className="w-full"
                onClick={() => handleExport(report.title)}
              >
                <Download className="w-4 h-4 mr-2" />
                Export as PDF
              </Button>
            </CardContent>
          </Card>
        ))}
      </div>
    </div>
  );
};
