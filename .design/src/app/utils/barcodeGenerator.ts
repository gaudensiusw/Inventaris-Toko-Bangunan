/**
 * Generate a random EAN-13 barcode
 * EAN-13 consists of 13 digits:
 * - First 3 digits: Country code (we'll use 899 for custom/internal use)
 * - Next 4-5 digits: Manufacturer code
 * - Next 3-4 digits: Product code
 * - Last digit: Check digit
 */
export const generateEAN13Barcode = (): string => {
  // Country code for custom use (899)
  const countryCode = '899';
  
  // Generate random manufacturer code (5 digits)
  const manufacturerCode = Math.floor(10000 + Math.random() * 90000).toString();
  
  // Generate random product code (4 digits)
  const productCode = Math.floor(1000 + Math.random() * 9000).toString();
  
  // Combine first 12 digits
  const first12Digits = countryCode + manufacturerCode + productCode;
  
  // Calculate check digit
  const checkDigit = calculateEAN13CheckDigit(first12Digits);
  
  return first12Digits + checkDigit;
};

/**
 * Calculate EAN-13 check digit
 */
const calculateEAN13CheckDigit = (digits: string): string => {
  let sum = 0;
  
  for (let i = 0; i < 12; i++) {
    const digit = parseInt(digits[i]);
    // Odd positions (1st, 3rd, 5th, etc.) are multiplied by 1
    // Even positions (2nd, 4th, 6th, etc.) are multiplied by 3
    sum += digit * (i % 2 === 0 ? 1 : 3);
  }
  
  const checkDigit = (10 - (sum % 10)) % 10;
  return checkDigit.toString();
};

/**
 * Validate EAN-13 barcode
 */
export const validateEAN13Barcode = (barcode: string): boolean => {
  // Check if barcode is exactly 13 digits
  if (!/^\d{13}$/.test(barcode)) {
    return false;
  }
  
  // Validate check digit
  const first12Digits = barcode.substring(0, 12);
  const checkDigit = barcode.substring(12);
  const calculatedCheckDigit = calculateEAN13CheckDigit(first12Digits);
  
  return checkDigit === calculatedCheckDigit;
};

/**
 * Generate a simpler sequential barcode based on timestamp
 * Format: TB-[YYYYMMDD]-[RANDOM5DIGITS]
 * TB = Toko Bangunan
 */
export const generateSimpleBarcode = (): string => {
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  const day = String(now.getDate()).padStart(2, '0');
  const random5 = Math.floor(10000 + Math.random() * 90000);
  
  return `TB-${year}${month}${day}-${random5}`;
};

/**
 * Generate barcode from SKU
 * Converts SKU to numeric barcode format
 */
export const generateBarcodeFromSKU = (sku: string): string => {
  // Remove non-alphanumeric characters
  const cleaned = sku.replace(/[^a-zA-Z0-9]/g, '');
  
  // Convert to numbers (simple hash)
  let numericCode = '';
  for (let i = 0; i < cleaned.length; i++) {
    const char = cleaned[i];
    if (/\d/.test(char)) {
      numericCode += char;
    } else {
      // Convert letter to number (A=1, B=2, etc.)
      numericCode += (char.toUpperCase().charCodeAt(0) - 64).toString().padStart(2, '0');
    }
  }
  
  // Ensure we have at least 12 digits, pad or truncate
  if (numericCode.length < 12) {
    numericCode = numericCode.padEnd(12, '0');
  } else if (numericCode.length > 12) {
    numericCode = numericCode.substring(0, 12);
  }
  
  // Add check digit
  const checkDigit = calculateEAN13CheckDigit(numericCode);
  
  return numericCode + checkDigit;
};

/**
 * Format barcode for display with spaces for readability
 * Example: 8991234567890 -> 899 12345 67890
 */
export const formatBarcodeDisplay = (barcode: string): string => {
  if (barcode.length === 13) {
    return `${barcode.substring(0, 3)} ${barcode.substring(3, 8)} ${barcode.substring(8)}`;
  }
  return barcode;
};
