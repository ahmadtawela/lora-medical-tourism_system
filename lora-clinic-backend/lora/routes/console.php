<?php

use Illuminate\Support\Facades\Schedule;

// يوميًا الساعة 6:00 صباحًا: وسم الفواتير المتأخرة + تنبيهات الدفع المتأخر
Schedule::command('invoices:check-overdue')->dailyAt('06:00');

// يوميًا الساعة 6:05 صباحًا: تنبيهات العمليات القادمة خلال 7 أيام
Schedule::command('notifications:check-upcoming-operations')->dailyAt('06:05');
