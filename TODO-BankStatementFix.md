# Bank Statement Extraction Fix TODO

## [ ] 1. Install Dependencies (Run as Admin in PowerShell)
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
choco install poppler ghostscript tesseract -y
Restart terminal/IDE for PATH update.

## [ ] 2. Verify installations
where pdftoppm
where gswin64c
tesseract --version

## [ ] 3. Update service file (next step)

## [ ] 4. Test upload PDF
php artisan serve (if not running)
Visit http://127.0.0.1:8000/bank-statement/create
Upload scanned PDF bank statement.

## [ ] 5. Complete

