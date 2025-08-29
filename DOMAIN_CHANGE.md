# 🔄 Domain Change: admin.larabus.com → admin.larabus.dev

## ✅ Changes Made

### 📁 **Folder Renamed**
```bash
mv ../admin.larabus.com ../admin.larabus.dev
```

### 📝 **Files Updated**

1. **Setup Scripts**:
   - `setup-management.php` → Updated domain variable
   - `setup-management-sqlite.php` → Updated domain variable and instructions
   
2. **Default Email**:
   - Changed from `admin@larabus.com` to `admin@larabus.dev`

### 🎯 **What This Affects**

- **Development URL**: Now use `http://localhost:8080` when running from `admin.larabus.dev/`
- **Production Setup**: Point your domain to `admin.larabus.dev/` folder instead of `admin.larabus.com/`
- **Documentation**: All references now use `.dev` domain

### 🚀 **Usage**

**Development:**
```bash
cd ../admin.larabus.dev
php -S localhost:8080 router.php
# Visit: http://localhost:8080
```

**Production:**
- Point `admin.yourdomain.dev` to the `admin.larabus.dev/` folder
- Or use any domain you prefer (the folder name is just for organization)

### 📋 **No Other Changes Needed**

- ✅ Configuration files work the same
- ✅ Database setup unchanged  
- ✅ All functionality identical
- ✅ The `.dev` extension is just cosmetic

The change from `.com` to `.dev` is complete and ready to use!
