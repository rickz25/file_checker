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
        ,['GROSS_SLS','d', 13]
        ,['VAT_AMNT','d', 13]
        ,['VATABLE_SLS','d', 13]
        ,['NONVAT_SLS','d', 13]
        ,['VATEXEMPT_SLS','d', 13]
        ,['VATEXEMPT_AMNT','d', 13]
        ,['LOCAL_TAX','d', 13]
        ,['PWD_DISC','d', 13]
        ,['SNRCIT_DISC','d', 13]
        ,['EMPLO_DISC','d', 13]
        ,['AYALA_DISC','d', 13]
        ,['STORE_DISC','d', 13]
        ,['OTHER_DISC','d', 13]
        ,['REFUND_AMT','d', 13]
        ,['SCHRGE_AMT','d', 13]
        ,['OTHER_SCHR','d', 13]
        ,['CASH_SLS','d', 13]
        ,['CARD_SLS','d', 13]
        ,['EPAY_SLS','d', 13]
        ,['DCARD_SLS','d', 13]
        ,['OTHERSL_SLS','d', 13]
        ,['CHECK_SLS','d', 13]
        ,['GC_SLS','d', 13]
        ,['MASTERCARD_SLS','d', 13]
        ,['VISA_SLS','d', 13]
        ,['AMEX_SLS','d', 13]
        ,['DINERS_SLS','d', 13]
        ,['JCB_SLS','d', 13]
        ,['GCASH_SLS','d', 13]
        ,['PAYMAYA_SLS','d', 13]
        ,['ALIPAY_SLS','d', 13]
        ,['WECHAT_SLS','d', 13]
        ,['GRAB_SLS','d', 13]
        ,['FOODPANDA_SLS','d', 13]
        ,['MASTERDEBIT_SLS','d', 13]
        ,['VISADEBIT_SLS','d', 13]
        ,['PAYPAL_SLS','d', 13]
        ,['ONLINE_SLS','d', 13]
        ,['OPEN_SALES','d', 13]
        ,['OPEN_SALES_2','d', 13]
        ,['OPEN_SALES_3','d', 13]
        ,['OPEN_SALES_4','d', 13]
        ,['OPEN_SALES_5','d', 13]
        ,['OPEN_SALES_6','d', 13]
        ,['OPEN_SALES_7','d', 13]
        ,['OPEN_SALES_8','d', 13]
        ,['OPEN_SALES_9','d', 13]
        ,['OPEN_SALES_10','d', 13]
        ,['OPEN_SALES_11','d', 13]
        ,['GC_EXCESS','d', 13]
        ,['MOBILE_NO','', 15]
        ,['NO_CUST','i', 6]
        ,['TRN_TYPE','s', 1]
        ,['SLS_FLAG','s', 1]
        ,['VAT_PCT','d', 6]
        ,['QTY_SLD','d3', 15]
    ],
    'item'=>[
        ['QTY','d3', 15]
        ,['ITEMCODE','s', 20]
        ,['PRICE','d', 13]
        ,['LDISC','d', 13]
    ]

];
