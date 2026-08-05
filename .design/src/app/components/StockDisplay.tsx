import React from 'react';
import { Product } from '../data/mockData';
import { getStockDisplay, formatStockSmart } from '../utils/unitConversion';
import { Badge } from './ui/badge';
import { Popover, PopoverContent, PopoverTrigger } from './ui/popover';
import { Info } from 'lucide-react';

interface StockDisplayProps {
  product: Product;
  showAllUnits?: boolean;
  compact?: boolean;
}

export const StockDisplay: React.FC<StockDisplayProps> = ({
  product,
  showAllUnits = false,
  compact = false,
}) => {
  const stockDisplays = getStockDisplay(product);
  const smartDisplay = formatStockSmart(product);

  if (compact) {
    return (
      <Popover>
        <PopoverTrigger asChild>
          <button className="inline-flex items-center gap-1 text-sm hover:text-blue-600 transition-colors">
            <span className="font-semibold">{smartDisplay}</span>
            <Info className="w-3 h-3 text-slate-400" />
          </button>
        </PopoverTrigger>
        <PopoverContent className="w-64 p-3" align="start">
          <div className="space-y-2">
            <p className="text-xs font-medium text-slate-600 mb-2">Detail Stok:</p>
            {stockDisplays.map((display, idx) => (
              <div
                key={idx}
                className="flex items-center justify-between text-sm py-1 border-b border-slate-100 last:border-0"
              >
                <span className="text-slate-600">{display.unit}:</span>
                <span className="font-medium">{display.formattedText}</span>
              </div>
            ))}
          </div>
        </PopoverContent>
      </Popover>
    );
  }

  if (showAllUnits) {
    return (
      <div className="space-y-2">
        {stockDisplays.map((display, idx) => (
          <div
            key={idx}
            className="flex items-center justify-between p-2 bg-slate-50 rounded"
          >
            <span className="text-sm text-slate-600 capitalize">{display.unit}:</span>
            <Badge variant="outline" className="font-mono">
              {display.formattedText}
            </Badge>
          </div>
        ))}
      </div>
    );
  }

  return (
    <div className="text-sm">
      <span className="font-semibold">{smartDisplay}</span>
    </div>
  );
};
