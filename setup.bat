@echo off
color 0A
echo =======================================================
echo     AUTO SETUP - MAPPING SWITCH PT. KBK (By: Gigha)
echo =======================================================
echo.

echo [1/5] Mengecek ketersediaan PHP...
php -v >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    color 0C
    echo [X] ERROR FATAL: PHP belum terinstal atau belum terdaftar di Environment Variables!
    echo Silakan install PHP terlebih dahulu.
    pause
    exit /b
)
echo [V] PHP Terdeteksi!
echo.

echo [2/5] Mengecek ketersediaan Composer...
composer -v >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    color 0C
    echo [X] ERROR FATAL: Composer belum terinstal!
    echo Silakan install Composer dari getcomposer.org.
    pause
    exit /b
)
echo [V] Composer Terdeteksi!
echo.

echo [3/5] Mengecek Ekstensi SQL Server (pdo_sqlsrv)...
php -m | findstr pdo_sqlsrv >nul 2>&1
IF %ERRORLEVEL% NEQ 0 (
    color 0E
    echo [!] PERINGATAN: Ekstensi 'pdo_sqlsrv' belum aktif di php.ini komputer ini.
    echo Aplikasi mungkin akan gagal terhubung ke database. Harap aktifkan terlebih dahulu!
) ELSE (
    echo [V] Driver SQL Server Terdeteksi!
)
echo.

echo [4/5] Menyiapkan file Environment (.env)...
IF NOT EXIST .env (
    copy .env.example .env >nul
    echo [V] File .env berhasil dibuat!
) ELSE (
    echo [V] File .env sudah ada.
)
echo.

echo [5/5] Menginstal Dependensi (Mohon tunggu, butuh internet)...
call composer install
echo.
call php artisan key:generate

color 0A
echo.
echo =======================================================
echo                 SETUP HAMPIR SELESAI!
echo =======================================================
echo Tuan Mentor, mohon lakukan 3 langkah manual ini:
echo 1. Buka file .env dan masukkan username 'sa' ^& password SQL Server.
echo 2. Pastikan TCP/IP di SQL Server Configuration Manager sudah ENABLED.
echo 3. Jalankan 'php artisan serve'
echo.
echo Panduan lengkap ada di file README.md
echo =======================================================
pause