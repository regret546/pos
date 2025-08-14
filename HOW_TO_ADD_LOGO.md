# 🖼️ How to Add Your Company Logo

## 📁 **Step 1: Upload Your Logo**

1. **Save your logo** as one of these formats:

   - `company-logo.png` (recommended)
   - `company-logo.jpg`
   - `company-logo.gif`

2. **Drag and drop** your logo file into this folder:

   ```
   extensions/tcpdf/pdf/images/
   ```

3. **Make sure** your logo is named: `company-logo.png` (or .jpg/.gif)

## ✏️ **Step 2: Edit the Receipt Template**

1. **Open this file**: `extensions/tcpdf/pdf/bill-a4.php`

2. **Find line 69** (around line 69):

   ```html
   <img src="images/company-logo.png" style="width:80px; height:80px;" />
   ```

3. **Replace your logo file name** if different:
   - If your logo is named differently, update `company-logo.png` to your filename
   - Supported formats: `.png`, `.jpg`, `.gif`

## 💡 **Quick Copy-Paste**

**Example:**

```html
<!-- If your logo is named "my-logo.jpg" -->
<img src="images/my-logo.jpg" style="width:80px; height:80px;" />

<!-- If your logo is named "company-logo.png" (default) -->
<img src="images/company-logo.png" style="width:80px; height:80px;" />
```

## 🎯 **Result**

- Your logo will appear in the top-left corner of the receipt
- Size: 80x80 pixels (perfect for the layout)
- The logo will be displayed on all printed receipts

## 📱 **Test It**

1. **Save the file** after making the change
2. **Go to Sales Management** in your POS
3. **Click the print button** 🖨️ for any sale
4. **Your A4 customized receipt** will open automatically!

---

**That's it! Your company logo will now appear on all receipts.** 🎉
