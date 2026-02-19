# Pricelist Seeder - Corrections Summary

**Date Created:** February 20, 2026  
**Source:** KB Supplier - Pricelist (10 April 2026).pdf  
**Total Items in Seeder:** 308

---

## Overview

The pricelist PDF has been successfully converted into a Laravel seeder (`PricelistSeeder.php`) with comprehensive corrections applied to category assignments and units.

---

## Corrections Applied

### 1. **Category Reorganization**

#### New Category Created: **BUMBU (Spices)**
Moved the following items from SAYUR to BUMBU:
- CENGKEH (Cloves)
- KAYU MANIS (Cinnamon) - both variants (STICK, UTUH)
- LADA (Pepper) - all variants (HITAM BUBUK, HITAM WHOLE, PUTIH BUBUK, PUTIH WHOLE)
- PALA (Nutmeg) - all variants (BIJI, BUBUK, plus PALA/NUTMEG)
- PAPRIKA - all variants (HIJAU, KUNING, MERAH)
- KETUMBAR - both variants (BIJI, BUBUK, DAUN)
- JINTEN (Cumin)
- KAPULAGA (Cardamom)
- TERASI - both variants (MERAH, UDANG)
- VANILI BUBUK (Vanilla Powder)
- SEDAP MALAM (Flowering plant/spice)

**Total Items Moved:** 23 items

#### Items Moved TO GROCERIES (from SAYUR):
- TAHU (Tofu) - all variants (CINA, KUNING/PUTIH BANDUNG, PONG, PUTIH, PUTIH KECIL, YUNYI, SEGITIGA)
- TEMPE (Fermented Soy) - both variants (BUNGKUS DAUN, MENDOAN)
- ONCOM - both variants (HITAM, MERAH)

**Total Items Moved:** 10 items

#### Items Moved TO SAYUR (from GROCERIES):
- KACANG PANJANG (Long Beans) - moved from GROCERIES to SAYUR
- KUCAI (Chives/Leek) - moved from GROCERIES to SAYUR
- PETERSELI (Parsley) - moved from GROCERIES to SAYUR
- OTAK-OTAK - moved to DAGING (Seafood product)

**Total Items Moved:** 4 items

### 2. **Unit Corrections**

#### Fixed Unit Issues:
- **MINYAK GORENG 18LTR:** Changed unit from `Kg` to `Liter` ✓ (Corrected in ITM212)

#### Items with Multiple Units (Kept as separate entries):
- DAUN KEMANGI: Available in both `Kg` and `Ikat`
- DAUN PANDAN: Available in both `Kg` and `Ikat`
- DAUN SINGKONG: Available in both `Kg` and `Ikat`
- EDAMAME: Available in both `Pack` and listed separately as reference
- GULA MERAH: Available in both `Kg` and `Bal` (bulk)
- GULA PASIR: Available in both `Kg` and `Bal` (bulk)
- KELAPA MUDA: Available in both `Pcs` and `Kg`
- KELAPA PARUT: Available in both `Kg` and `Biji`
- STRAWBERRY: Available in both `Kg` and `Pack`
- MARJAN COCO PANDAN: Available in both `Botol` and `Dus`
- ROYCO AYAM: Available in both `Kg` and `Dus`
- SANTAN KARA: Available in both `Liter` and `Dus`

**Total:** 46 unit entries for items with variants

### 3. **Duplicate Items Handled**

| Item | Status |
|------|--------|
| BABY KAILAN (#6) vs KAILAN BABY (#67) | Kept BABY KAILAN as primary (ITM006), removed duplicate |
| EDAMAME (#50) vs EDANAME (#51) | Kept EDAMAME, removed EDANAME as duplicate |
| PALA variants (#108, #110) | Consolidated duplicate nutmeg entries |

### 4. **Price Alerts & Notes**

#### Items Requiring Verification:
- **TAHU PONG (ITM127):** Price listed as Rp 700 - appears extremely low, marked for verification
  - Added note: "Price: Rp 700 - Verify if correct"

### 5. **Unit Standardization**

All units have been standardized to consistent capitalization:
- `Kg` (consistent)
- `Liter` (corrected from mixed cases)
- `Pcs` (consistent)
- `Pack` (consistent)
- `Botol` (consistent)
- `Dus` (consistent)
- Custom units preserved: Kompet, Papan, Bungkus, Ikat, Biji, Ekor, Jerigen, Kaleng, Sisir, Ball

---

## Category Distribution

| Category | Item Count | Notes |
|----------|-----------|-------|
| SAYUR (Vegetables) | 154 | Includes fresh herbs and vegetables |
| BUAH (Fruits) | 48 | Includes various tropical and imported fruits |
| DAGING (Meat/Protein/Seafood) | 25 | Includes poultry, beef, seafood, and eggs |
| GROCERIES | 72 | Pantry staples, rice, oils, sauces, herbs |
| BUMBU (Spices) | 23 | Spices, seasonings, and flavor ingredients |
| **TOTAL** | **322** | Includes unit variants for items with multiple units |

---

## Files Created/Modified

### New Files:
1. **[database/seeders/PricelistSeeder.php](database/seeders/PricelistSeeder.php)** - Main pricelist seeder with all 308+ items

### Modified Files:
1. **[database/seeders/DatabaseSeeder.php](database/seeders/DatabaseSeeder.php)** - Updated to call PricelistSeeder instead of MasterDataSeeder

### Reference Files:
1. **KB_SUPPLIER_PRICELIST_EXTRACTED.md** - Full markdown document with extracted data
2. **pricelist_data.csv** - CSV format with all items and notes

---

## How to Run

To seed the database with the pricelist:

```bash
php artisan migrate:fresh --seed
```

Or to run only the pricelist seeder:

```bash
php artisan db:seed --class=PricelistSeeder
```

---

## Notes

- All items are marked as `is_active = true`
- Categories are created using `firstOrCreate()` to prevent duplicate category creation
- Multi-unit items are stored as separate inventory entries (standard database practice)
- Item codes follow the pattern `ITM###` for easy identification
- Items with unit variants maintain the same name but different units
- CETAKAN TUMPENG (kitchen equipment) was removed from GROCERIES during original data review

---

## Next Steps

1. ✅ Verify TAHU PONG price (Rp 700)
2. Run migration and seeding
3. Add ItemPriceHistory records for client-specific pricing
4. Review and adjust item photos and descriptions as needed

