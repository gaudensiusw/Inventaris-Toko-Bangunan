import React, { useState, useEffect } from 'react';
import { Product } from '../data/mockData';
import { convertToBaseUnit, convertFromBaseUnit, getAllUnits, getPriceForUnit } from '../utils/unitConversion';
import { Input } from './ui/input';
import { Label } from './ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';

interface UnitQuantityInputProps {
  product: Product;
  value: number; // Always in base unit
  onChange: (baseQuantity: number) => void;
  defaultUnit?: string;
  label?: string;
  showPrice?: boolean;
  pricePerBaseUnit?: number;
}

export const UnitQuantityInput: React.FC<UnitQuantityInputProps> = ({
  product,
  value,
  onChange,
  defaultUnit,
  label = 'Quantity',
  showPrice = false,
  pricePerBaseUnit,
}) => {
  const units = getAllUnits(product);
  const [selectedUnit, setSelectedUnit] = useState(defaultUnit || units[0]);
  const [displayQuantity, setDisplayQuantity] = useState(0);

  // Update display quantity when value or unit changes
  useEffect(() => {
    const converted = convertFromBaseUnit(product, value, selectedUnit);
    setDisplayQuantity(converted);
  }, [value, selectedUnit, product]);

  const handleQuantityChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const inputValue = parseFloat(e.target.value) || 0;
    setDisplayQuantity(inputValue);
    
    // Convert to base unit and notify parent
    const baseQty = convertToBaseUnit(product, inputValue, selectedUnit);
    onChange(baseQty);
  };

  const handleUnitChange = (newUnit: string) => {
    // Convert current base value to new unit
    const converted = convertFromBaseUnit(product, value, newUnit);
    setSelectedUnit(newUnit);
    setDisplayQuantity(converted);
  };

  const unitPrice = showPrice && pricePerBaseUnit 
    ? getPriceForUnit(product, pricePerBaseUnit, selectedUnit)
    : null;

  return (
    <div className="space-y-2">
      <Label>{label}</Label>
      <div className="flex gap-2">
        <div className="flex-1">
          <Input
            type="number"
            value={displayQuantity}
            onChange={handleQuantityChange}
            min="0"
            step="0.01"
            placeholder="0"
          />
        </div>
        <div className="w-32">
          <Select value={selectedUnit} onValueChange={handleUnitChange}>
            <SelectTrigger>
              <SelectValue />
            </SelectTrigger>
            <SelectContent>
              {units.map(unit => (
                <SelectItem key={unit} value={unit}>
                  {unit}
                </SelectItem>
              ))}
            </SelectContent>
          </Select>
        </div>
      </div>
      
      <div className="flex items-center justify-between text-xs text-slate-600">
        <span>
          = {value.toLocaleString('id-ID', { maximumFractionDigits: 2 })} {product.base_unit}
        </span>
        {unitPrice && (
          <span className="font-medium text-slate-700">
            @ Rp {unitPrice.toLocaleString('id-ID')} / {selectedUnit}
          </span>
        )}
      </div>
    </div>
  );
};
