## Email PTDH SPRING GEOVOS
Dokumentasi terkait sistem emailing PTDH SPRING GEOVOS. Terdapat 2 sistem emailing, hourly email dan alert email.

---

### 1. Hourly Email
**File List**
```
1. email.php
2. emailjob.sh
3. mail.log
```
**File Description**
1. email.php
   File ini adalah file utama yang menjalankan hourly email dengan mengambil data pada api.Kemudian dikirimkan ke email yang tertera pada script tersebut.
2. emailjob.sh
   File ini adalah file bash untuk menjalankan script pada cronjob di crontab -e.
3. mail.log
   File ini adalah log file yang berisi data timestamp, request dan response dari script email.php

### 2. Alert Email
**File List**
```
1. alert.php
2. alert.sh
3. alertmailstatus.log
4. apirequest.log
5. config_alert.php
6. data.alert
7. templates/alert_mail.html
```
**File Description**
1. alert.php
   File ini adalah file utama yang menjalankan alert email dengan mengambil data pada api.Kemudian memproses kondisi ph sebelum dikirimkan ke email yang tertera pada config_alert.php tersebut.
2. alert.sh
   File ini adalah file bash untuk menjalankan script pada cronjob di crontab -e.
3. config_alert.php
   File ini adalah config file untuk setting threshold seperti batas pH dan interval alert mail (warning & critical).
4. apirequest.log
   File ini adalah log file yang berisi timestamp, request dan response pada api.
5. alertmailstatus.log
   File ini adalah log file yang berisi timestamp dan status pengiriman alert email.
6. data.alert
   File ini adalah text file untuk menyimpan state dari variabel yang digunakan pada alert.php untuk memproses alert berdasarkan pH.
7. templates/alert_mail.html
   Template alert email yang ditampilkan pada email.

### 3. Lain-lain
**File List**
```
1. /PHPMailer-master
2. /save
```
**File Description**
1. /PHPMailer-master
   Library php untuk mengirim email.
2. /save
   Backup script.
---
### Cronjob
**File yang digunakan**
```
1. alert.sh
2. emailjob.sh
```
**Cara menambahkan cronjob**
Jalankan command dibawah ini:
```
$ crontab -e
```
Apabila sudah terbuka, tekan tombol i, kemudian tambahkan command berikut pada baris paling akhir (contoh): **ketentuan/aturan cron disesuaikan*
```
* * * * * command 
```
Run job every hour at 0 minute.
Ex:
```
0 * * * * /bin/sh /var/www/html/ptdhspring/email.sh
```
Kemudian save dengan menekan ESC dan ketik :wq .