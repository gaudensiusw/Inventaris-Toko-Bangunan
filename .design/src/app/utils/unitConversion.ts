import { Product, UnitConversion } from '../data/mockData';

/**
 * Convert quantity from one unit to base unit
 */
export function convertToBaseUnit(
  product: Product,
  quantity: number,
  fromUnit: string
): number {
  if (fromUnit === product.base_unit) {
    return quantity;
  }

  const unit = product.units.find(u => u.unit_name === fromUnit);
  if (!unit) {
    throw new Error(`Unit "${fromUnit}" not found for product "${product.name}"`);
  }

  return quantity * unit.conversion_to_base;
}

/**
 * Convert quantity from base unit to target unit
 */
export function convertFromBaseUnit(
  product: Product,
  baseQuantity: number,
  toUnit: string
): number {
  if (toUnit === product.base_unit) {
    return baseQuantity;
  }

  const unit = product.units.find(u => u.unit_name === toUnit);
  if (!unit) {
    throw new Error(`Unit "${toUnit}" not found for product "${product.name}"`);
  }

  return baseQuantity / unit.conversion_to_base;
}

/**
 * Convert quantity between two units
 */
export function convertBetweenUnits(
  product: Product,
  quantity: number,
  fromUnit: string,
  toUnit: string
): number {
  const baseQuantity = convertToBaseUnit(product, quantity, fromUnit);
  return convertFromBaseUnit(product, baseQuantity, toUnit);
}

/**
 * Get formatted stock display in all available units
 */
export function getStockDisplay(product: Product): {
  unit: string;
  quantity: number;
  formattedText: string;
}[] {
  const displays: { unit: string; quantity: number; formattedText: string }[] = [];

  // Base unit
  displays.push({
    unit: product.base_unit,
    quantity: product.current_stock,
    formattedText: `${product.current_stock.toLocaleString('id-ID')} ${product.base_unit}`,
  });

  // Converted units - ensure units array exists
  if (product.units && Array.isArray(product.units)) {
    product.units.forEach(unit => {
      const convertedQty = convertFromBaseUnit(product, product.current_stock, unit.unit_name);
      const wholeUnits = Math.floor(convertedQty);
      const remainder = product.current_stock - (wholeUnits * unit.conversion_to_base);

      let formattedText = '';
      if (remainder > 0) {
        formattedText = `${wholeUnits} ${unit.unit_name} + ${remainder} ${product.base_unit}`;
      } else {
        formattedText = `${wholeUnits} ${unit.unit_name}`;
      }

      displays.push({
        unit: unit.unit_name,
        quantity: convertedQty,
        formattedText,
      });
    });
  }

  return displays;
}

/**
 * Get the default purchase unit for a product
 */
export function getDefaultPurchaseUnit(product: Product): UnitConversion | null {
  if (!product.units || !Array.isArray(product.units)) return null;
  return product.units.find(u => u.is_default_purchase) || null;
}

/**
 * Get the default sale unit for a product
 */
export function getDefaultSaleUnit(product: Product): UnitConversion | null {
  if (!product.units || !Array.isArray(product.units)) return null;
  return product.units.find(u => u.is_default_sale) || null;
}

/**
 * Calculate price for a specific unit
 */
export function getPriceForUnit(
  product: Product,
  pricePerBaseUnit: number,
  unit: string
): number {
  if (unit === product.base_unit) {
    return pricePerBaseUnit;
  }

  if (!product.units || !Array.isArray(product.units)) {
    throw new Error(`No units defined for product "${product.name}"`);
  }

  const unitConversion = product.units.find(u => u.unit_name === unit);
  if (!unitConversion) {
    throw new Error(`Unit "${unit}" not found for product "${product.name}"`);
  }

  return pricePerBaseUnit * unitConversion.conversion_to_base;
}

/**
 * Format stock for display with smart unit selection
 */
export function formatStockSmart(product: Product): string {
  const defaultSaleUnit = getDefaultSaleUnit(product);
  
  if (defaultSaleUnit) {
    const convertedQty = convertFromBaseUnit(product, product.current_stock, defaultSaleUnit.unit_name);
    const wholeUnits = Math.floor(convertedQty);
    const remainder = product.current_stock - (wholeUnits * defaultSaleUnit.conversion_to_base);

    if (remainder > 0 && remainder >= defaultSaleUnit.conversion_to_base * 0.1) {
      // Show remainder if it's more than 10% of a unit
      return `${wholeUnits} ${defaultSaleUnit.unit_name} + ${remainder} ${product.base_unit}`;
    } else {
      return `${wholeUnits} ${defaultSaleUnit.unit_name}`;
    }
  }

  return `${product.current_stock.toLocaleString('id-ID')} ${product.base_unit}`;
}

/**
 * Validate if a unit is available for a product
 */
export function isValidUnit(product: Product, unit: string): boolean {
  if (unit === product.base_unit) return true;
  if (!product.units || !Array.isArray(product.units)) return false;
  return product.units.some(u => u.unit_name === unit);
}

/**
 * Get all available units for a product (including base unit)
 */
export function getAllUnits(product: Product): string[] {
  if (!product.units || !Array.isArray(product.units)) {
    return [product.base_unit];
  }
  return [product.base_unit, ...product.units.map(u => u.unit_name)];
}