Feature: Analyse computers

  Scenario: Get available cpu
    When I send GET request to "/analyse" with following json:
    """
    {
      "cpu": g500,
      "motherboard_vendor": "P5B P965"
    }
    """
    Then the JSON response should have "rating"
    And the JSON response at "rating" should be a number