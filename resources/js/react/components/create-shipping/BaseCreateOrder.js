import React from 'react';
import { Formik } from 'formik';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { APIContext } from '../../api';

class BaseCreateOrder extends React.Component {
  static contextType = APIContext;

  constructor(props) {
    super(props);

    this.state = {
      initialValues: this.getInitialValues(props.shippingInfo)
    };

    this.formik = React.createRef();

    this.onSubmit = this.onSubmit.bind(this);
    this.renderForm = this.renderForm.bind(this);
    this.getInputProps = this.getInputProps.bind(this);

    this.getInitialValues = this.getInitialValues.bind(this);
    this.getValidationSchema = this.getValidationSchema.bind(this);
  }

  getInitialValues(data) {
    return { ...data };
  }

  getValidationSchema() {
    return {};
  }

  getCreateValues(data) {
    return data;
  }

  renderForm(formik, getInputProps) {
    return null;
  }

  getInputProps(formik) {
    const { errors, touched, values } = formik;

    return (name) => {
      let value = values[name];

      return {
        name,
        value,
        checked: Boolean(value),
        selected: Boolean(values[name]),
        onBlur: formik.handleBlur,
        onChange: (val) => formik.setFieldValue(name, val),
        className: touched[name] && errors[name] ? 'has-error' : null,
        help: touched[name] ? errors[name] : null
      };
    };
  }

  onSubmit(values) {
    let _values = this.getCreateValues(values);

    const { onCreateSuccess } = this.props;
    const { createShippingOrder } = this.context;

    createShippingOrder('ghn', _values)
      .then(res => {
        onCreateSuccess(res);
      });
  }

  render() {
    const { initialValues } = this.state;

    return (
      <Formik
        innerRef={this.formik}
        initialValues={initialValues}
        validationSchema={this.getValidationSchema()}
        validateOnBlur={true}
        onSubmit={this.onSubmit}
      >
        {(formik) => (
          <form onSubmit={formik.handleSubmit}>
            {this.renderForm(formik, this.getInputProps(formik))}

            <footer className="components-form__buttons">
              <Button
                isPrimary
                onClick={formik.handleSubmit}
                isBusy={formik.isSubmitting}
                disabled={!formik.isValid}
              >
                Submit
              </Button>
            </footer>
          </form>
        )}
      </Formik>
    );
  }
}

export default BaseCreateOrder;
