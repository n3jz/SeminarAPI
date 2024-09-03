eMonitoring
Seminar razvoj programske opreme v TK


Opis projekta:
Prvi odjemalec je centralni nadzorni sistem zgradbe. Ta periodično pošilja podatke o merilnikih v REST API spletno storitev. Drugi odjemalec je spletna aplikacija, ki je namenjena spremljanju rabe električne energije. Uporabnik se prijavi in lahko izbira objekte. Prikažejo se mu merilniki električne energije na tem objektu. Vnese lahko začetni in končni čas podatkov o povprečni moči in skupni energiji, ki se prikažejo v dveh grafih.

stack: LAMP (bitnami image),
PHP 8.2.16,
Apache 2.4.58 (Unix),
mysql from 11.2.3-MariaDB, client 15.2 for Linux (x86_64) using readline 5.1.

