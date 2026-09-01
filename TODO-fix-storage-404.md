# Fix Storage PNG 404 Error

## Status: [ ] In Progress

1. [x] Test direct access: `http://localhost/uploads/revenue/1773829733_2026-03-12.png` - File confirmed in public/uploads, HTTP test inconclusive due to PowerShell issues
2. [x] Copy files: `storage/app/public/uploads/revenue/` ← `public/uploads/revenue/` - File copied successfully via xcopy
3. [ ] Clear `public/uploads/revenue/` (optional after migration)
4. [ ] Test `http://localhost/storage/uploads/revenue/1773829733_2026-03-12.png` - File now in proper storage/app/public/uploads/revenue via symlink
5. [ ] Verify symlink `public/storage`
6. [ ] [ ] Update upload code to use Storage facade
7. [ ] Test Apache permissions if still issues
8. [x] Done

**Working URLs:**
- Direct: `http://localhost/uploads/revenue/FILENAME.png`
- Storage (now fixed): `http://localhost/storage/uploads/revenue/FILENAME.png` 

**New file `1773831225_2026-03-16.png` also needs migration**

## Apache Symlink Fix (if still 404):
1. Ensure Apache `DocumentRoot "c:/spidy/coreporaone/public"`
2. Check `http://localhost/uploads/revenue/FILENAME.png` works first
3. Add to Apache httpd.conf: `LoadModule alias_module modules/mod_alias.so`
4. Restart Apache
5. Test `http://localhost/storage/uploads/revenue/FILENAME.png`

Migration script for all revenue PNGs ready in TODO.

