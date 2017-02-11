Feature: Motherboards

  Background:
    Given the following motherboards exist:
      | id | moth_vendor  | cpu_vendor       | cpu_socket  | memory_type |
      | 1  | ASUS         | Intel            | LGA1150     | DDR3 DIMM   |
      | 2  | ECS          | AMD              | S370        | DDR3 DIMM   |


  Scenario: Get available motherboards
    When I send GET request to "/computers/motherboards" with following json:
    """
    {
      "page": 1,
      "pageSize": 2
    }
    """
    Then I should get following json response:
    """
    [
      {
        "moth_vendor": "ASUS",
        "cpu_vendor": "Intel",
        "cpu_socket": "LGA1150",
        "memory_type": "DDR3 DIMM"
      },
      {
        "moth_vendor": "ECS",
        "cpu_vendor": "AMD",
        "cpu_socket": "S370",
        "memory_type": "DDR3 DIMM"
      }
    ]
    """
