import * as Yup from 'yup';
import { __ } from '@wordpress/i18n';

export const validationSchema = Yup.object({
  name: Yup
    .string()
    .required(__('Required', 'vn-shipping')),

  phone: Yup
    .number(__('Vui lòng nhập đúng SĐT', 'vn-shipping'))
    .required(__('Required', 'vn-shipping')),

  service_id: Yup
    .number(__('Trường này là bắt buộc', 'vn-shipping'))
    .required(__('Required', 'vn-shipping'))
});

export const requiredNoteOptions = [
  { label: 'Không cho xem hàng', value: 'KHONGCHOXEMHANG' },
  { label: 'Cho xem hàng', value: 'CHOXEMHANGKHONGTHU' },
  { label: 'Cho thử hàng', value: 'CHOTHUHANG' }
];
