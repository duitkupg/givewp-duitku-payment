/**
 * Start with a Self-Executing Anonymous Function (IIFE) to avoid polluting and conflicting with the global namespace (encapsulation).
 * @see https://developer.mozilla.org/en-US/docs/Glossary/IIFE
 *
 * This won't be necessary if you're using a build system like webpack.
 */
(() => {
  let settings = {};
  /**
   * Example of rendering gateway fields (without jsx).
   *
   * This renders a simple div with a label and input.
   *
   * @see https://react.dev/reference/react/createElement
   */
  const pluginUrl = GiveDuitkuData.pluginUrl;
  function DuitkuGatewayFields() {
    return window.wp.element.createElement(
      "div",
      {
        className: "duitku-tokopedia-card-payment-help-text",
      },
      window.wp.element.createElement(
        "p",
        {
          style: { marginBottom: 10 },
        },
        settings.message ||
          "You will be redirected to Duitku Tokopedia Card Payment Payment Gateway."
      ),
      window.wp.element.createElement(
        "img",
        {
          style: { 
            display: "block",
            margin: "0 auto",
            marginBottom: 0,
            width : 300,
            height : 75,
           },
           src : pluginUrl + "assets/images/tokopedia.png",
           alt : "Tokopedia Logo",
        },
      ),
    );
  }

  /**
   * Example of a front-end gateway object.
   */
  const DuitkuGateway = {
    id: "T1",
    initialize() {
      settings = this.settings;
    },
    Fields() {
      return window.wp.element.createElement(DuitkuGatewayFields);
    },
  };

  /**
   * The final step is to register the front-end gateway with GiveWP.
   */
  window.givewp.gateways.register(DuitkuGateway);
})();
