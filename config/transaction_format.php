<?php
/**
 * s => String
 * i => Integer/number
 * d => Double with 2 decimal places
 * f => Float
 * d3 =>Double with 3 decimal places
 * 
 * index 0 = Data Field
 * index 1 = Data Type
 * index 2 = Data Length
 * index 3 = Field Description
*/
return 
[
    'header'=>[
         ['CCCODE','s', 20]
        ,['MERCHANT_NAME','s', 60]
        ,['TRN_DATE','s', 10]
        ,['NO_TRN','i', 6]
    ],
    'transaction' =>[
         ['CDATE','s', 10]
        ,['TRN_TIME','s', 8]
        ,['TER_NO','i', 3]
        ,['TRANSACTION_NO','i', 15]
        ,['GROSS_SLS','d', 12]
        ,['VAT_AMNT','d', 12]
        ,['VATABLE_SLS','d', 12]
        ,['NONVAT_SLS','d', 12]
        ,['VATEXEMPT_SLS','d', 12]
        ,['VATEXEMPT_AMNT','d', 12]
        ,['LOCAL_TAX','d', 12]
        ,['PWD_DISC','d', 12]
        ,['SNRCIT_DISC','d', 12]
        ,['EMPLO_DISC','d', 12]
        ,['AYALA_DISC','d', 12]
        ,['STORE_DISC','d', 12]
        ,['OTHER_DISC','d', 12]
        ,['REFUND_AMT','d', 12]
        ,['SCHRGE_AMT','d', 12]
        ,['OTHER_SCHR','d', 12]
        ,['CASH_SLS','d', 12]
        ,['CARD_SLS','d', 12]
        ,['EPAY_SLS','d', 12]
        ,['DCARD_SLS','d', 12]
        ,['OTHERSL_SLS','d', 12]
        ,['CHECK_SLS','d', 12]
        ,['GC_SLS','d', 12]
        ,['MASTERCARD_SLS','d', 12]
        ,['VISA_SLS','d', 12]
        ,['AMEX_SLS','d', 12]
        ,['DINERS_SLS','d', 12]
        ,['JCB_SLS','d', 12]
        ,['GCASH_SLS','d', 12]
        ,['PAYMAYA_SLS','d', 12]
        ,['ALIPAY_SLS','d', 12]
        ,['WECHAT_SLS','d', 12]
        ,['GRAB_SLS','d', 12]
        ,['FOODPANDA_SLS','d', 12]
        ,['MASTERDEBIT_SLS','d', 12]
        ,['VISADEBIT_SLS','d', 12]
        ,['PAYPAL_SLS','d', 12]
        ,['ONLINE_SLS','d', 12]
        ,['OPEN_SALES','d', 12]
        ,['OPEN_SALES_2','d', 12]
        ,['OPEN_SALES_3','d', 12]
        ,['OPEN_SALES_4','d', 12]
        ,['OPEN_SALES_5','d', 12]
        ,['OPEN_SALES_6','d', 12]
        ,['OPEN_SALES_7','d', 12]
        ,['OPEN_SALES_8','d', 12]
        ,['OPEN_SALES_9','d', 12]
        ,['OPEN_SALES_10','d', 12]
        ,['OPEN_SALES_11','d', 12]
        ,['GC_EXCESS','d', 12]
        ,['MOBILE_NO','', 15]
        ,['NO_CUST','i', 6]
        ,['TRN_TYPE','s', 1]
        ,['SLS_FLAG','s', 1]
        ,['VAT_PCT','d', 6]
        ,['QTY_SLD','d3', 12]
    ],
    'item'=>[
        ['QTY','d3', 12]
        ,['ITEMCODE','s', 20]
        ,['PRICE','d', 12]
        ,['LDISC','d', 12]
    ]

];
