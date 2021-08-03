import React from 'react';
import * as PropTypes from 'prop-types';

import Select from 'react-select';

import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { compose } from '@wordpress/compose';
import { withSelect } from '@wordpress/data';

import { STORE_NAME } from '../../store/constants';

const mapAddressToOptions = (data) => data.map((d) => ({
  value: d.code, label: d.name_with_type
}));

const reducer = (state, action) => {
  switch (action.type) {
    case 'SET_LOADING':
      return {
        ...state,
        isLoading: {
          ...state.isLoading,
          ...action.isLoading
        }
      };
    case 'SET_OPTIONS':
      return {
        ...state,
        options: {
          ...state.options,
          ...action.options
        }
      };
    case 'SET_VALUES':
      let _values = {};
      let _options = {};

      switch (action.name) {
        case 'province':
          _options = {
            districts: [],
            wards: []
          };

          _values = {
            province: action.value,
            district: null,
            ward: null
          };
          break;

        case 'district':
          _options = {
            ...state.options,
            wards: []
          };

          _values = {
            ...state.values,
            district: action.value,
            ward: null
          };
          break;

        case 'ward':
          _options = { ...state.options };
          _values = { ...state.values, ward: action.value };
          break;
      }

      return {
        ...state,
        values: _values,
        options: _options
      };
    default:
      throw new Error();
  }
};

class AddressSelect extends React.Component {
  constructor(props) {
    super(props);

    const values = props.value || {};

    this.state = {
      values: {
        province: values.province || null,
        district: values.district || null,
        ward: values.ward || null
      },
      isLoading: {
        district: false,
        wards: false
      },
      options: {
        districts: [],
        wards: []
      }
    };

    this.onChangeCallback = this.onChangeCallback.bind(this);
    this.loadDistrict = this.loadDistrict.bind(this);
    this.loadWards = this.loadWards.bind(this);
  }

  componentDidMount() {
    const { values } = this.state;

    if (values.province) {
      this.loadDistrict();
    }

    if (values.province && values.district) {
      this.loadWards();
    }
  }

  dispatch(action, callback) {
    this.setState(reducer(this.state, action), callback);
  }

  loadDistrict() {
    const { values } = this.state;
    let province = values.province;

    this.dispatch({ type: 'SET_LOADING', isLoading: { district: true } });

    apiFetch({ path: `/awethemes/vn-shipping/address/${province}` })
      .then(response => {
        const districts = mapAddressToOptions(response);

        this.dispatch({ type: 'SET_OPTIONS', options: { districts } });
      })
      .finally(() => {
        this.dispatch({ type: 'SET_LOADING', isLoading: { district: false } });
      });
  };

  loadWards() {
    const { values } = this.state;

    let province = values.province;
    let district = values.district;

    this.dispatch({ type: 'SET_LOADING', isLoading: { wards: true } });

    apiFetch({ path: `/awethemes/vn-shipping/address/${province}/${district}` })
      .then(response => {
        const wards = mapAddressToOptions(response);

        this.dispatch({ type: 'SET_OPTIONS', options: { wards } });
      })
      .finally(() => {
        this.dispatch({ type: 'SET_LOADING', isLoading: { wards: false } });
      });
  };

  onChangeCallback(name, currentOption) {
    const { onChange } = this.props;

    this.dispatch(
      { type: 'SET_VALUES', name, value: currentOption.value },
      () => {
        if (onChange) {
          onChange(this.state.values);
        }

        if (name === 'province') {
          this.loadDistrict();
        } else if (name === 'district') {
          this.loadWards();
        }
      }
    );
  }

  render() {
    const { provincesOptions } = this.props;
    const { values, options, isLoading } = this.state;

    let [provinceValue, districtValue, wardValue] = [null, null, null];
    const find = (obj, test) => obj.find(a => parseInt(a.value, 10) === parseInt(test, 10));

    if (values.province && provincesOptions.length) {
      provinceValue = find(provincesOptions, values.province);
    }

    if (values.district && options.districts.length) {
      districtValue = find(options.districts, values.district);
    }

    if (values.ward && options.wards.length) {
      wardValue = find(options.wards, values.ward);
    }

    return (
      <div className="address-group">
        <Select
          name="province"
          value={provinceValue}
          options={provincesOptions}
          onChange={(value) => this.onChangeCallback('province', value)}
          isLoading={!provincesOptions.length}
          isDisabled={!provincesOptions.length}
          placeholder={__('Select province...', 'vn-shipping')}
          isClearable={false}
          isSearchable
        />

        <Select
          name="district"
          value={districtValue}
          options={options.districts}
          onChange={(value) => this.onChangeCallback('district', value)}
          isLoading={isLoading.district}
          isDisabled={isLoading.province || options.districts.length === 0}
          placeholder={__('Select district...', 'vn-shipping')}
          isClearable={false}
          isSearchable
        />

        <Select
          name="ward"
          value={wardValue}
          options={options.wards}
          isLoading={isLoading.wards}
          onChange={(value) => this.onChangeCallback('ward', value)}
          isDisabled={isLoading.province || options.wards.length === 0}
          placeholder={__('Select ward...', 'vn-shipping')}
          isClearable={false}
          isSearchable
        />
      </div>
    );
  }
}

AddressSelect.propTypes = {
  value: PropTypes.any,
  onChange: PropTypes.func,
  provincesOptions: PropTypes.array.isRequired
};

export default compose([
  withSelect((select) => {
    const { getProvinces } = select(STORE_NAME);

    return {
      provincesOptions: mapAddressToOptions(getProvinces())
    };
  })
])(AddressSelect);
