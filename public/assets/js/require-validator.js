define(['validator-core', 'validator-lang'], function (Validator, undefined) {
    Validator.config({
        rules: {
            bankcard: function(element, param) {
                var value = element.value.replace(/\s/g, ''),
                    isValid = true,
                    rFormat = /^[\d]{12,19}$/;

                if ( !rFormat.test(value) ) {
                    isValid = false;
                } else {
                    var arr = value.split('').reverse(),
                        i = arr.length,
                        temp,
                        sum = 0;

                    while ( i-- ) {
                        if ( i%2 === 0 ) {
                            sum += +arr[i];
                        } else {
                            temp = +arr[i] * 2;
                            sum += temp % 10;
                            if ( temp > 9 ) sum += 1;
                        }
                    }
                    if ( sum % 10 !== 0 ) {
                        isValid = false;
                    }
                }
                return isValid || "����д��Ч�����п���";
            },
            unique: function(element, param) {
                var name = $(element).attr("ng-model");
                name = name.split(".");
                if (!name || name.length != 2)
                    return true;

                var value = element.value.replace(/\s/g, '');
                var model = $(element).data("rule-model");
                var modelid = $(element).data("rule-model-id");
                return $.ajax({
                    url: 'ajax/check',
                    type: 'post',
                    data: {
                        name:name[1],
                        value:value,
                        model:model,
                        id:modelid,
                    },
                    dataType: 'json'
                });

            },
            idcard: function(element, param) {
                var value = element.value,
                    isValid = true;
                var cityCode = {11:"����",12:"���",13:"�ӱ�",14:"ɽ��",15:"���ɹ�",21:"����",22:"����",23:"������ ",31:"�Ϻ�",32:"����",33:"�㽭",34:"����",35:"����",36:"����",37:"ɽ��",41:"����",42:"���� ",43:"����",44:"�㶫",45:"����",46:"����",50:"����",51:"�Ĵ�",52:"����",53:"����",54:"���� ",61:"����",62:"����",63:"�ຣ",64:"����",65:"�½�",71:"̨��",81:"���",82:"����",91:"���� "};

                /* 15λУ����� (dddddd yymmdd xx g)    g����Ϊ�У�ż��ΪŮ
                 * 18λУ����� (dddddd yyyymmdd xxx p) xxx����Ϊ�У�ż��ΪŮ��pУ��λ

                 У��λ��ʽ��C17 = C[ MOD( ��(Ci*Wi), 11) ]
                 i----��ʾ�����ַ������������У�������ڵ�λ�����
                 Wi 7 9 10 5 8 4 2 1 6 3 7 9 10 5 8 4 2 1
                 Ci 1 0 X 9 8 7 6 5 4 3 2
                 */
                var rFormat =/^\d{6}(19|20)\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$|^\d{6}\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}$/;    // ��ʽ��֤

                if ( !rFormat.test(value) || !cityCode[value.substr(0,2)] ) {
                    isValid = false;
                }
                // 18λ���֤��Ҫ��֤���һλУ��λ
                else if (value.length === 18) {
                    var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];    // ��Ȩ����
                    var Ci = "10X98765432"; // У���ַ�
                    // ��Ȩ���
                    var sum = 0;
                    for (var i = 0; i < 17; i++) {
                        sum += value.charAt(i) * Wi[i];
                    }
                    // ����У��ֵ
                    var C17 = Ci.charAt(sum % 11);
                    // ��У��λ�ȶ�
                    if ( C17 !== value.charAt(17) ) {
                        isValid =false;
                    }
                }
                return isValid || "����д��ȷ�����֤����";
            }
        }
    });
    return Validator;
});